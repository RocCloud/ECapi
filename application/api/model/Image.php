<?php

namespace app\api\model;


class Image extends BaseModel
{
    protected $hidden = ['id','from','delete_time','update_time'];

    //读取器用于处理字段
    protected function getUrlAttr($value,$data){
        return $this->prefixImgUrl($value,$data);
    }
}
