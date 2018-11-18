<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/9/21
 * Time: 16:39
 */

namespace app\api\validate;


class IDCollection extends BaseValidate
{
    protected $rule=[
        'ids'=>'require|checkIDs',
    ];

    protected $message=[
        'ids'=>'ids参数必须是以逗号分隔的多个正整数',
    ];

    public function checkIDs($value){
        $values= explode(',',$value);
        if(empty($values)){
            return false;
        }
        foreach ($values as $v){
            $res=$this->isPostiveInteger($v);
            if(!$res){
                return false;
            }
        }
        return true;
    }

}