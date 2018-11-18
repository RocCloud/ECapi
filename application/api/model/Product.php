<?php

namespace app\api\model;

class Product extends BaseModel
{
    protected $hidden = ['delete_time','create_time','update_time','pivot','from','category_id'];

    public function productImage(){
        return $this->hasMany('ProductImage','product_id','id');
    }

    public function productProperty(){
        return $this->hasMany('ProductProperty','product_id','id');
    }

    protected function getMainImgUrlAttr($value,$data){
        return $this->prefixImgUrl($value,$data);
    }

    public static function getMostRecent($count){
        return self::limit($count)->order('create_time desc')->select();
    }

    public static function getProductsByCategoryID($categoryID){
        return self::where('category_id','=',$categoryID)->select();
    }

    public static function getProductDetail($id){
        return self::with([
                'productImage'=>function($query){
                    $query->with(['image'])->order('order','asc');
                }
            ])
            ->with(['productProperty'])
            ->find($id);
    }
}
