<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/10/1
 * Time: 20:31
 */

namespace app\api\controller;


use think\Controller;
use app\api\service\Token as TokenService;

class BaseController extends Controller
{
    protected function checkPrimaryScope(){
        TokenService::needPrimaryScope();
    }

    protected function checkExclusiveScope(){
        TokenService::needExclusiveScope();
    }

}