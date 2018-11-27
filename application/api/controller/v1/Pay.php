<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/10/3
 * Time: 17:33
 */

namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\service\Pay as PayService;
use app\api\service\WxNotify as WxNotifyService;
use app\api\validate\IDMustBePostiveInt;

class Pay extends BaseController
{
    protected $beforeActionList = [
      'checkExclusiveScope' => ['only' => 'getPreOrder']
    ];

    public function getPreOrder($id=''){
        (new IDMustBePostiveInt())->goCheck();
        $payService=new PayService($id);
        return $payService->pay();
    }

    public function receiveNotify(){
        $wxNotify = new WxNotifyService();
        $wxNotify->Handle();
    }
}