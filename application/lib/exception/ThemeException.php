<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/9/24
 * Time: 14:12
 */

namespace app\lib\exception;


class ThemeException extends BaseException
{
    //HTTP 状态码
    public $code = 404;
    //错误具体信息
    public $msg = '指定的主题不存在，请检查主题ID';
    //自定义的错误代码
    public $errorCode = 30000;
}