<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/10/31
 * Time: 20:23
 */

namespace app\api\service;


use app\api\model\ThirdApp as ThirdAppModel;
use app\lib\exception\TokenException;

class AppToken extends Token
{
    public function get($ac,$se){
        $app = ThirdAppModel::check($ac,$se);
        if(!$app){
            throw new TokenException([
                'msg'=>'授权失败',
                'errorCode'=>10004
            ]);
        }else{
            $scope = $app->scope;
            $uid = $app->id;
            $values = [
              'scope' => $scope,
              'uid' => $uid
            ];
            $token = $this->saveToCache($values);
            return $token;
        }
    }

    private  function saveToCache($values){
        $token = self::generateToken();
        $value = json_encode($values);
        $expire_in = config('setting.token_expire_in');

        $res=cache($token,$value,$expire_in);
        if(!$res){
            throw new TokenException([
                'msg'=>'服务器缓存异常',
                'errorCode'=>10005
            ]);
        }
        return $token;
    }
}