<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/9/28
 * Time: 20:44
 */

namespace app\api\controller\v1;


use app\api\service\AppToken as AppTokenService;
use app\api\service\UserToken;
use app\api\validate\AppTokenGet;
use app\api\validate\TokenGet;
use app\lib\exception\ParameterException;
use app\api\service\Token as TokenService;

class Token
{
    public function getToken($code=''){
        (new TokenGet())->goCheck();
        $userToken=new UserToken($code);
        $token=$userToken->get();
        return [
            'token' => $token
        ];
    }

    //校验token
    public function verifyToken($token=''){
        if(!$token){
            throw new ParameterException([
                'token不允许为空'
            ]);
        }
        $valid = TokenService::verifyToken($token);
        return [
          'isValid' => $valid
        ];
    }

    /*
     * 第三方应用获取令牌
     * @url /app_token?
     * @POST  ac=:ac   se=:secret
     * */
    public function getAppToken($ac='',$se=''){
        (new AppTokenGet())->goCheck();
        $app=new AppTokenService();
        $token=$app->get($ac,$se);
        return[
            'token'=>$token
        ];
    }
}