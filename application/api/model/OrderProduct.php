<?php

namespace app\api\model;

class OrderProduct extends BaseModel
{
    protected $hidden = ['delete_time','update_time'];

    public static function getDataByOrderID($orderID){
        return self::where('order_id','=',$orderID)->select();
    }
}
