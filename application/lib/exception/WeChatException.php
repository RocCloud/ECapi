<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/9/24
 * Time: 14:12
 */

namespace app\lib\exception;


class WeChatException extends BaseException
{
    //HTTP 状态码
    public $code = 400;
    //错误具体信息
    public $msg = '微信服务器接口调用失败';
    //自定义的错误代码
    public $errorCode = 999;
}