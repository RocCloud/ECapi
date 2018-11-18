<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/9/24
 * Time: 14:12
 */

namespace app\lib\exception;


class SuccessMessage
{
    //HTTP 状态码
    public $code = 201;
    //错误具体信息
    public $msg = 'ok';
    //自定义的错误代码
    public $errorCode = 0;
}