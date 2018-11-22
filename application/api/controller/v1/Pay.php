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
use think\Log;

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
//        $xmlData = file_get_contents('php://input');
//        log::init([
//             'type' => 'File',
//             'path' => LOG_PATH.'error/',
//              'level' => []
//         ]);
//        Log::record($xmlData, 'error');
    }
}