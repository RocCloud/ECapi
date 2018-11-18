<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/9/30
 * Time: 17:54
 */

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\model\User as UserModel;
use app\api\model\UserAddress;
use app\api\service\Token as TokenService;
use app\api\validate\AddressNew;
use app\lib\exception\SuccessMessage;
use app\lib\exception\UserException;


class Address extends BaseController
{
    //前置方法设置
    protected $beforeActionList = [
        'checkPrimaryScope' => ['only' => 'createOrUpdateAddress,getUserAddress']
    ];

    //获取用户自己的收获地址
    public function getUserAddress(){
        $uid=TokenService::getCurrentUid();
        $userAddress=UserAddress::where('user_id',$uid)->find();
        if(!$userAddress){
            throw new UserException([
                'msg' => '用户地址不存在',
                'errorCode' => 60001
            ]);
        }
        return $userAddress;
    }

    public function createOrUpdateAddress(){
        $validate=new AddressNew();
        $validate->goCheck();
        $uid =  TokenService::getCurrentUid();
        $user=UserModel::get($uid);
        if(!$user){
            throw new UserException();
        }
        $dataArray = $validate->getDataByRule(input('post.'));

        $address=$user->address;
        if($address){
            $user->address->save($dataArray);
        }else{
            $user->address()->save($dataArray);
        }
        return json(new SuccessMessage(),201);
    }
}