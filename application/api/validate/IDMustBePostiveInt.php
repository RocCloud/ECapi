<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/9/21
 * Time: 16:39
 */

namespace app\api\validate;


class IDMustBePostiveInt extends BaseValidate
{
    protected $rule=[
        'id'=>'require|isPostiveInteger',
    ];

    protected $message=[
        'id'=>'id必须是正整数',
    ];

    protected function isPostiveInteger($value,$rule='',$date='',$field=''){
        if(is_numeric($value) && is_int($value+0) && ($value+0)>0){
            return true;
        }else{
            return $field.'必须是正整数';
        }
    }
}