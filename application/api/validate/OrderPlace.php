<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/10/1
 * Time: 21:37
 */

namespace app\api\validate;


use app\lib\exception\ParameterException;

class OrderPlace extends BaseValidate
{
    protected $rule = [
        'products' => 'checkProducts'
    ];

    protected $singleRule = [
        'product_id' => 'require|isPostiveInteger',
        'count' => 'require|isPostiveInteger'
    ];

    protected function checkProducts($value){
        if(!is_array($value)){
            throw new ParameterException([
                'msg' => '商品列表格式不正确'
            ]);
        }
        if(empty($value)){
            throw new ParameterException([
                'msg' => '商品列表不能为空'
            ]);
        }
        foreach ($value as $v){
            $this->checkProduct($v);
        }
        return true;
    }

    protected function checkProduct($value){
        $validate = new BaseValidate($this->singleRule);
        $res=$validate->check($value);
        if(!$res){
            throw new ParameterException([
                'msg' => '商品列表参数错误'
            ]);
        }
    }
}