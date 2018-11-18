<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/11/1
 * Time: 13:16
 */

namespace app\api\service;


use think\Exception;

class AccessToken
{
    private $tokenUrl;
    const TOKEN_CACHED_KEY ='access';
    const TOKEN_EXPIRE_IN = 7000;

    function __construct()
    {
        $url = config('wx.access_token_url');
        $url = sprintf($url,config('wx.app_id'),config('wx.app_secret'));
        $this->tokenUrl = $url;
    }

    public function get(){
        $token = $this->getFromCache();
        if(!$token){
            return $this->getFromWxServer();
        }else{
            return $token;
        }
    }

    //从缓存中获取access_token
    private function getFromCache(){
        $token = cache(self::TOKEN_CACHED_KEY);
        if($token){
            return $token;
        }
        return null;
    }

    //从微信服务器中获取access_token
    private function getFromWxServer(){
        $token = curl_get($this->tokenUrl);
        $token = json_decode($token,true);
        if(!$token){
            throw new Exception('获取AccessToken异常');
        }
        if(!empty($token['errcode'])){
            throw new Exception($token['errcode']);
        }
        $this->saveToCache($token);
    }

    //将access_token存入缓存
    private function saveToCache($token){
        cache(self::TOKEN_CACHED_KEY,$token,self::TOKEN_EXPIRE_IN);
    }
}