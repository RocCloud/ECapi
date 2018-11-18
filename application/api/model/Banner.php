<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/9/23
 * Time: 21:29
 */

namespace app\api\model;


class Banner extends BaseModel
{
    //也可在外部（例控制器）中调用 $banner->hidden(['',''])方法隐藏,但database配置中resultset_type 值应该设为 collection
    protected $hidden = ['delete_time','update_time'];
    //外键在主表里用belongsTo,外键在从表里用has~
    public function bannerItem(){
       return $this->hasMany('BannerItem','banner_id','id');
    }
    public static function getBannerByID($id){
        $banner=self::with(['bannerItem','bannerItem.image'])->find($id);
        return $banner;
    }
}