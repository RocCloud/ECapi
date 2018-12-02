<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/10/1
 * Time: 20:30
 */

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\validate\IDMustBePostiveInt;
use app\api\validate\OrderPlace;
use app\api\service\Token as TokenService;
use app\api\service\Order as OrderService;
use app\api\validate\PagingParameter;
use app\api\model\Order as OrderModel;
use app\api\validate\StatusMustBePostiveInt;
use app\lib\exception\OrderException;
use app\lib\exception\SuccessMessage;

class Order extends BaseController
{
    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'placeOrder'],
        'checkPrimaryScope' => ['only' => 'getDetail,getSummaryByUser']
    ];

    public function placeOrder(){
        (new OrderPlace())->goCheck();
        $uid=TokenService::getCurrentUid();
        $oProducts=input('post.products/a');
        $orderService=new OrderService();
        $status=$orderService->place($uid,$oProducts);
        return $status;
    }

    //用户分页查询历史订单
    public function getSummaryByUser($page=1,$size=15){
        (new PagingParameter())->goCheck();
        $uid = TokenService::getCurrentUid();
        $pagingOrders=OrderModel::getSummaryByUser($uid,$page,$size);
        if($pagingOrders->isEmpty()){
            return [
              'data' => [],
              'current_page' => $page,
            ];
        }else{
            $data=$pagingOrders->hidden(['snap_items','snap_address','prepay_id'])->toArray();
            return [
                'data' => $data,
                'current_page' => $page,
            ];
        }
    }

    //用户根据订单状态分页查询历史订单
    public function getSummaryByStatus($page=1,$size=3,$status,$flag=false){
        (new PagingParameter())->goCheck();
        (new StatusMustBePostiveInt())->goCheck();
        $uid = TokenService::getCurrentUid();
        $condition = [];
        if($flag){
            $condition['user_id'] = array('eq',$uid);
            $condition['status'] = array(array('eq',5),array('eq',$status), 'or');
        }else{
            $condition['user_id'] = array('eq',$uid);
            $condition['status'] = array('eq',$status);
        }
        $pagingOrders=OrderModel::getSummaryByStatus($condition,$page,$size);
        if($pagingOrders->isEmpty()){
            return [
                'data' => [],
                'current_page' => $page,
            ];
        }else{
            $data=$pagingOrders->hidden(['snap_items','snap_address','prepay_id'])->toArray();
            return [
                'data' => $data,
                'current_page' => $page,
            ];
        }
    }

    //获取订单详情
    public function getDetail($id){
        (new IDMustBePostiveInt())->goCheck();
        $orderDetail=OrderModel::get($id);
        if(!$orderDetail){
            throw new OrderException();
        }
        return $orderDetail->hidden(['prepay_id']);
    }


    /*
     * 获取所有订单简要信息（分页）
     * @param int $page
     * @param int $size
     * @return array
     * @throw \app\lib\exception\ParameterException
     * */
    public function getSummary($page=1,$size=20){
        (new PagingParameter())->goCheck();
        $pagingOrders=OrderModel::getSummaryByPage($page,$size);
        if($pagingOrders->isEmpty()){
            return [
                'data' => [],
                'current_page' => $page,
            ];
        }else{
            $data=$pagingOrders->hidden(['snap_items','snap_address'])->toArray();
            return [
                'data' => $data,
                'current_page' => $page,
            ];
        }
    }

    //发送模板消息
    public function delivery($id){
        //var_dump($id);die;
        (new IDMustBePostiveInt())->goCheck();
        $order = new OrderService();
        $success = $order->delivery($id);
        if($success){
            return new SuccessMessage();
        }
    }
}