<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/10/1
 * Time: 22:26
 */

namespace app\api\service;


use app\api\model\Order as OrderModel;
use app\api\model\OrderProduct as OrderProductModel;
use app\api\model\OrderProduct;
use app\api\model\Product;
use app\api\model\UserAddress;
use app\common\lib\delayqueue\DelayQueue;
use app\lib\enum\OrderStatusEnum;
use app\lib\exception\OrderException;
use app\lib\exception\UserException;
use think\Db;
use think\Exception;
use app\common\lib\payovertime\MyRedis;

class Order
{
    protected $products;
    protected $oProducts;
    protected $uid;

    public  function place($uid,$oProducts){
        $this->products = $this->getProductsByOrder($oProducts);
        $this->oProducts = $oProducts;
        $this->uid = $uid;
        $status=$this->getOrderStatus();
        if(!$status['pass']){
            $status['order_id'] = -1;
            return  $status;
        }
        $orderSnap=$this->snapOrder($status);
        $order=$this->createOrder($orderSnap);
        $order['pass'] = true;
        return $order;
    }

    //创建订单
    private function createOrder($snap){
        try {
            Db::startTrans();
            $orderNo = self::makeOrderNo();
            $order = OrderModel::create([
                'order_no' => $orderNo,
                'user_id' => $this->uid,
                'total_price' => $snap['orderPrice'],
                'total_count' => $snap['totalCount'],
                'snap_img' => $snap['snapImg'],
                'snap_name' => $snap['snapName'],
                'snap_items' => $snap['pStatus'],
                'snap_address' => $snap['snapAddress'],
            ]);

            foreach ($this->oProducts as &$p) {
                $p['order_id'] = $order['id'];
            }
            $orderProduct = new OrderProductModel();
            $orderProduct->saveAll($this->oProducts);
            Db::commit();

            //将order_id存入redis，用做后续订单未支付自动回库
/*            DelayQueue::getInstance('close_order')->addTask(
                'app\common\lib\delayqueue\job\CloseOrder', // 自己实现的job
                strtotime('+1 minute'), // 订单失效时间
                ['order_id' => $order->id] // 传递给job的参数
            );*/
            $redis = new MyRedis();
            $redis->setex($order->id,60,$order->id);

            return [
                'order_no' => $orderNo,
                'order_id' => $order->id,
                'create_time' => $order->create_time
            ];
        }catch (Exception $ex){
            Db::rollback();
            throw $ex;
        }
    }

    //生成订单编号
    public static function makeOrderNo(){
        $yCode = array('A','B','C','D','E','F','G','H','I','J');
        $orderSn = $yCode[intval(date('Y'))-2018].strtoupper(dechex(date('m'))).date(
            'd').substr(time(),-5).substr(microtime(),2,5).sprintf('%02d',rand(0,99));
        return $orderSn;
    }

    //生成订单快照
    private function snapOrder($status){
        $snap = [
          'orderPrice' => 0,
          'totalCount' => 0,
          'pStatus' => [],
          'snapAddress' =>null,
          'snapName' => '',
          'snapImg' => ''
        ];
        $snap['orderPrice'] = $status['orderPrice'];
        $snap['totalCount'] = $status['totalCount'];
        $snap['pStatus'] = json_encode($status['pStatusArray']);
        $snap['snapAddress'] = json_encode($this->getUserAddress());
        $snap['snapName'] = $this->products[0]['name'];
        $snap['snapImg'] = $this->products[0]['main_img_url'];
        if(count($this->products)>1){
            $snap['snapName'] .= '等';
        }
        return $snap;
    }

    private function getUserAddress(){
        $address=UserAddress::where('user_id','=',$this->uid)->find();
        if(!$address){
            throw new UserException([
                'msg'=>'用户收货地址不存在，下单失败',
                'errorCode'=>60001
            ]);
        }
        return $address;
    }

    //根据订单信息查询真实商品信息
    private function getProductsByOrder($oProducts){
        $oPIDs=[];
        foreach ($oProducts as $item){
            array_push($oPIDs,$item['product_id']);
        }
        $products=Product::all($oPIDs);
        $products->visible(['id','price','stock','name','main_img_url']);
        return $products;
    }

    //获取当前订单生成情况
    private function getOrderStatus(){
        $status=[
            'pass'=>true,
            'orderPrice'=>0,
            'totalCount'=>0,
            'pStatusArray'=>[]
        ];
        foreach($this->oProducts as $oProduct){
            $pStatus=$this->getProductStatus($oProduct['product_id'],$oProduct['count'],$this->products);
            if(!$pStatus['haveStock']){
                $status['pass'] = false;
            }
            $status['orderPrice'] += $pStatus['totalPrice'];
            $status['totalCount'] += $pStatus['counts'];
            array_push($status['pStatusArray'],$pStatus);
        }
        return $status;
    }

    private function getProductStatus($oPID,$oCount,$products){
        $product = null;
        $pStatus = [
            'id'=>null,
            'haveStock'=>false,
            'counts'=>0,
            'price'=>0,
            'name'=>'',
            'totalPrice'=>0,
            'main_img_url'=>null
        ];
        foreach ($products as $v){
            if($oPID==$v['id']){
                $product=$v;
            }
        }
        if($product){
            $pStatus['id']=$product['id'];
            $pStatus['name']=$product['name'];
            $pStatus['counts']=$oCount;
            if($oCount <= $product['stock'] ){
                $pStatus['haveStock'] = true;
            }
            $pStatus['price']=$product['price'];
            $pStatus['main_img_url']=$product['main_img_url'];
            $pStatus['totalPrice']=$oCount * $product['price'];
        }else{
            throw new OrderException([
                'msg'=>'id为'.$oPID.'的商品不存在，创建订单失败'
            ]);
        }
        return $pStatus;
    }

    public function checkOrderStock($orderID){
        $oProducts=OrderProductModel::where('order_id','=',$orderID)->select();
        $this->oProducts = $oProducts;
        $this->products=$this->getProductsByOrder($oProducts);
        return $this->getOrderStatus();
    }

    //发送模板消息
    public function delivery($orderID,$jumpPage=''){
        $order=OrderModel::where('id','=',$orderID)->find();
        if(!$order){
            throw new OrderException();
        }
        if($order->status != OrderStatusEnum::PAID){
            throw new OrderException([
                'msg' => '订单未付款或已更新',
                'errorCode' => 80002,
                'code' => 403
            ]);
        }
        $order->status = OrderStatusEnum::DELIVERED;
        $order->save();
        $message = new DeliveryMessage();
        return $message->sendDeliveryMessage($order,$jumpPage);
    }
}