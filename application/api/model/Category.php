<?php

namespace app\api\model;

class Category extends BaseModel
{
    protected $hidden = ['delete_time','update_time','create_time','topic_img_id'];

    public function image(){
        return $this->belongsTo('image','topic_img_id','id');
    }
}
