<?php

namespace app\api\model;
use app\lib\enum\OrderStatusEnum;

class Order extends BaseModel
{
    protected $hidden = ['user_id','delete_time','update_time'];
    protected $autoWriteTimestamp = true;

    public function getSnapItemsAttr($value){
        if(empty($value)){
            return null;
        }
        return json_decode($value);
    }

    public function getSnapAddressAttr($value){
        if(empty($value)){
            return null;
        }
        return json_decode($value);
    }

    public static function getSummaryByUser($uid,$page=1,$size=15){
        return self::where('user_id','=',$uid)->order('create_time desc')->paginate($size,true,['page'=>$page]);
    }

    public static function getSummaryByStatus($status,$uid,$page=1,$size=15){
        return self::where('user_id','=',$uid)->where('status','=',$status)->order('create_time desc')->paginate($size,true,['page'=>$page]);
    }

    public static function getSummaryByPage($page=1,$size=20){
        return self::order('create_time desc')->paginate($size,true,['page'=>$page]);
    }

    public static function PaymentDelay($orderID){
        return self::where('id','=',$orderID)->update(['status' => OrderStatusEnum::Pay_Overtime]);
    }

    public static function getOrderByID($orderID){
        return self::where('id','=',$orderID)->find();
    }
}
