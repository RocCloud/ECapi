<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/9/24
 * Time: 14:12
 */

namespace app\lib\exception;


class TokenException extends BaseException
{
    //HTTP 状态码
    public $code = 401;
    //错误具体信息
    public $msg = 'Token已过期或无效Token';
    //自定义的错误代码
    public $errorCode = 10001;
}