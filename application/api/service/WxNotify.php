<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/10/5
 * Time: 1:57
 */

namespace app\api\service;

use app\api\model\Product as ProductModel;
use app\lib\enum\OrderStatusEnum;
use think\Db;
use think\Exception;
use think\Loader;
use app\api\model\Order as OrderModel;
use app\api\service\Order as OrderService;
use think\Log;

Loader::import('WxPay.WxPay',EXTEND_PATH,'.Api.php');

class WxNotify extends \WxPayNotify
{
    public function NotifyProcess($data, &$msg){
        if($data['result_code'] == 'SUCCESS'){
            $orderNo = $data['out_trade_no'];
            Db::startTrans();
            try {
                $order = OrderModel::where('order_no', '=', $orderNo)->find();
                if ($order->status == 1) {
                    $orderService=new OrderService();
                    $stockStatus=$orderService->checkOrderStock($order->id);
                    if($stockStatus['pass']){
                        $this->updateOrderStatus($order->id,true);
                        $this->reduceStock($stockStatus);
                    }else{
                        $this->updateOrderStatus($order->id,false);
                    }
                }
                Db::commit();
                return true;
            }catch (Exception $ex){
                Db::rollback();
                Log::record($ex,'error');
                return false;
            }
        }else {
            //如果支付结果失败，返回true用于停止继续调用
            return true;
        }
    }

    //消减库存
    private function reduceStock($stockStatus){
        foreach ($stockStatus['pStatusArray'] as $singlePStatus){
            ProductModel::where('id','=',$singlePStatus['id'])->SetDec('stock',$singlePStatus['count']);
        }
    }

    //更新订单状态
    private function updateOrderStatus($orderID,$passStatus){
        $status = $passStatus ? OrderStatusEnum::PAID :OrderStatusEnum::PAID_BUT_OUT_OF;
        OrderModel::where('id','=',$orderID)->update(['status'=>$status]);
    }
}