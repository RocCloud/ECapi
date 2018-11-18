<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/9/28
 * Time: 20:57
 */

namespace app\api\service;


use app\lib\enum\ScopeEnum;
use app\lib\exception\WeChatException;
use app\api\model\User as UserModel;
use think\Exception;

class UserToken extends Token
{
    protected $code;
    protected $wxAppID;
    protected $wxAppSecret;
    protected $wxLoginUrl;
    public function __construct($code)
    {
        $this->code = $code;
        $this->wxAppID = config('wx.app_id');
        $this->wxAppSecret = config('wx.app_secret');
        $this->wxLoginUrl = sprintf(config('wx.login_url'),$this->wxAppID,$this->wxAppSecret,$this->code);
    }

    public function get(){
        $res=curl_get($this->wxLoginUrl);
        $wxRes=json_decode($res,true);
        if(empty($wxRes)){
            throw new Exception('获取session_key及openID时异常，微信内部错误');
        }else{
            $loginFail = array_key_exists('errcode',$wxRes);
            if($loginFail){
                $this->processLoginError($wxRes);
            }else{
                return $this->grantToken($wxRes);
            }
        }
    }

    //颁发令牌
    private function grantToken($wxRes){
        //获取openid,依此查询数据库中是否已经存在该用户
        $openid = $wxRes['openid'];
        $user = UserModel::getByOpenID($openid);
        if($user){
            $uid = $user->id;
        }else{
            $uid = $this->newUser($openid);
        }
        //组合缓存数据
        $cacheValue = $this->prepareCacheValue($wxRes,$uid);
        $token = $this->saveToCache($cacheValue);
        return $token;
    }

    //将token数据存入缓存
    private function saveToCache($cacheValue){
        $key = self::generateToken();
        $value = json_encode($cacheValue);
        $expire_in = config('setting.token_expire_in');

        cache($key,$value,$expire_in);
        return $key;
    }

    //组合缓存的值
    private function prepareCacheValue($wxRes,$uid){
        $cacheValue = $wxRes;
        $cacheValue['uid'] = $uid;
        $cacheValue['scope'] = ScopeEnum::user;
        return $cacheValue;
    }

    private function newUser($openid){
        $user=UserModel::create([
            'openid'=>$openid
        ]);
        return $user->id;
    }

    private function processLoginError($wxRes){
        throw new WeChatException([
            'errorCode' => $wxRes['errcode'],
            'msg' => $wxRes['errmsg']
        ]);
    }
}