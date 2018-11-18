<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/10/3
 * Time: 17:39
 */

namespace app\api\service;


use app\lib\enum\OrderStatusEnum;
use app\lib\exception\OrderException;
use app\lib\exception\TokenException;
use think\Exception;
use app\api\service\Order as OrderService;
use app\api\model\Order as OrderModel;
use app\api\service\Token as TokenService;
use think\Loader;
use think\Log;

//引入微信SDK支付
Loader::import('WxPay.WxPay',EXTEND_PATH,'.Api.php');

class Pay
{
    private $orderID;
    private $orderNo;

    public function __construct($orderID)
    {
        if(!$orderID){
            throw new Exception('订单号不允许为空');
        }
        $this->orderID = $orderID;
    }

    public function pay(){
       $this->checkOrderValid();
       $orderService = new OrderService();
       $status=$orderService->checkOrderStock($this->orderID);
       if(!$status['pass']){
           return $status;
       }
       return $this->makeWxPreOrder($status['orderPrice']);
    }

    //创建微信预支付订单信息
    private function makeWxPreOrder($totalPrice){
        $openid=Token::getCurrentTokenVar('openid');
        if(!$openid){
            throw new TokenException();
        }
        $wxOrderData = new \WxPayUnifiedOrder();
        $wxOrderData->SetOut_trade_no($this->orderNo);//设置订单号
        $wxOrderData->SetTrade_type('JSAPI');//交易类型 （JSAPI 公众号支付）
        $wxOrderData->SetTotal_fee($totalPrice*100);//订单总金额，单位为分
        $wxOrderData->SetBody('零食商贩');//商品简单描述
        $wxOrderData->SetOpenid($openid);
        $wxOrderData->SetNotify_url(config('secure.pay_back_url'));//微信回调地址
        return $this->getPaySignature($wxOrderData);
    }

    //向微信发送获取预支付订单
    private function getPaySignature($wxOrderData){
        $wxOrder = \WxPayApi::unifiedOrder($wxOrderData);

        if($wxOrder['return_code'] != 'SUCCESS' || $wxOrder['result_code'] != 'SUCCESS'){
            Log::record($wxOrder,'error');
            Log::record('获取预支付订单失败','error');
        }

        $this->recordPreOrder($wxOrder);
        return $this->sign($wxOrder);
    }

    //生成客户端调用微信支付接口需要的参数
    private function sign($wxOrder){
        $jsApiPayData = new \WxPayJsApiPay();
        $jsApiPayData->SetAppid(config('wx.app_id'));
        $jsApiPayData->SetTimeStamp((string)time());

        $rand = md5(time().mt_rand(0,1000));
        $jsApiPayData->SetNonceStr($rand);

        $jsApiPayData->SetPackage('prepay_id='.$wxOrder['prepay_id']);
        $jsApiPayData->SetSignType('md5');

        $sign = $jsApiPayData->MakeSign();
        $rawValues = $jsApiPayData->GetValues();
        $rawValues['paySign'] = $sign;
        unset($rawValues['appId']);

        return $rawValues;
    }

    //处理微信预订单id
    private function recordPreOrder($wxOrder){
        OrderModel::where('id','=',$this->orderID)->update(['prepay_id'=>$wxOrder['prepay_id']]);
    }

    //检测订单是否有效
    private function checkOrderValid(){
        $order = OrderModel::where('id','=',$this->orderID)->find();
        if(!$order){
            throw new OrderException();
        }
        if(!TokenService::isValidOperate($order->user_id)){
            throw new TokenException([
                'msg'=>'订单与用户不匹配',
                'errorCode'=>10003
            ]);
        };
        if($order->status != OrderStatusEnum::UNPAID){
            throw new OrderException([
                'msg'=>'订单已支付过',
                'errorCode'=>80003,
                'code'=>400
            ]);
        }
        $this->orderNo = $order->order_no;
        return true;
    }
}