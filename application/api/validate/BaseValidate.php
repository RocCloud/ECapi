<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/9/21
 * Time: 17:29
 */

namespace app\api\validate;


use app\lib\exception\ParameterException;
use think\Validate;

class BaseValidate extends Validate
{
    public function goCheck(){
        $param=input('param.');
        //批量验证参数
        $res=$this->batch()->check($param);
        if(!$res){
            //抛出自定义的验证器异常类
            $e = new ParameterException([
                'msg' => $this->error
            ]);
            throw $e;
        }else{
            return true;
        }
    }

    protected function isPostiveInteger($value,$rule='',$date='',$field=''){
        if(is_numeric($value) && is_int($value+0) && ($value+0)>0){
            return true;
        }else{
            return false;
        }
    }

    protected function isNotEmpty($value,$rule='',$date='',$field=''){
        if(empty($value)){
            return false;
        }else{
            return true;
        }
    }

    protected function isMobile($value,$rule='',$date='',$field=''){
        $rule='^1(3|4|5|7|8)[0-9]\d{8}$^';
        $res=preg_match($rule,$value);
        if($res){
            return true;
        }else{
            return false;
        }
    }

    public function getDataByRule($array){
        if(array_key_exists('user_id',$array)|array_key_exists('uid',$array)){
            throw new ParameterException([
                'msg'=>'参数中含有非法参数名user_id或uid'
            ]);
        }
        $arrayNew=[];
        foreach ($this->rule as $k => $v){
            $arrayNew[$k] = $array[$k];
        }
        return $arrayNew;
    }
}