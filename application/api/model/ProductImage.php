<?php

namespace app\api\model;

class ProductImage extends BaseModel
{
    protected $hidden = ['delete_time','img_id','product_id'];

    public function Image(){
        return $this->belongsTo('Image','img_id','id');
    }
}
