<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/9/23
 * Time: 21:25
 */

namespace app\lib\exception;


class BannerMissException extends BaseException
{
    //HTTP 状态码
    public $code = 404;

    //错误具体信息
    public $msg = '请求的Banner不存在';

    //自定义的错误代码
    public $errorCode = 40000;
}