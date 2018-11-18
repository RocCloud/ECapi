<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/9/28
 * Time: 20:57
 */

namespace app\api\service;


use app\lib\enum\ScopeEnum;
use app\lib\exception\ForbiddenException;
use app\lib\exception\TokenException;
use think\Cache;
use think\Exception;
use think\Request;

class Token
{
    public static function generateToken(){
        $randChar = getRandChar(32);
        $timestamp = $_SERVER['REQUEST_TIME_FLOAT'];
        $salt = config('secure.token_salt');
        return md5($randChar.$timestamp.$salt);
    }

    public static function getCurrentTokenVar($key){
        $token=Request::instance()->header('token');

        $tokenVar=Cache::get($token);
        if(!$tokenVar){
            throw new TokenException();
        }else{
            if(!is_array($tokenVar)){
                $tokenVar=json_decode($tokenVar,true);
            }
            if(array_key_exists($key,$tokenVar)){
                return $tokenVar[$key];
            }else{
                throw new Exception('尝试获取的Token值不存在');
            }
        }
    }

    public static function getCurrentUid(){
        return self::getCurrentTokenVar('uid');
    }

    //需要用户或cms管理员权限
    public static function needPrimaryScope(){
        $scope=self::getCurrentTokenVar('scope');
        if($scope){
            if($scope >= ScopeEnum::user){
                return true;
            }else{
                throw new ForbiddenException();
            }
        }else{
            throw new TokenException();
        }
    }

    //只要用户权限
    public static function needExclusiveScope(){
        $scope=self::getCurrentTokenVar('scope');
        if($scope){
            if($scope == ScopeEnum::user){
                return true;
            }else{
                throw new ForbiddenException();
            }
        }else{
            throw new TokenException();
        }
    }

    //检查用户是否为当前登陆用户
    public static function isValidOperate($checkedUID){
        if(!$checkedUID){
            throw new Exception('检查UID时必须传入一个被检查的UID');
        }
        $currentOperateUID=self::getCurrentUid();
        if($currentOperateUID == $checkedUID){
            return true;
        }else{
            return false;
        }
    }

    ///校验token
    public static function verifyToken($token){
        $exist = Cache::get($token);
        if($exist){
            return true;
        }else{
            return false;
        }
    }
}