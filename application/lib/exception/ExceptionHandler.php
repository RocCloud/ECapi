<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/9/23
 * Time: 21:18
 */

namespace app\lib\exception;


use Exception;
use think\exception\Handle;
use think\Log;
use think\Request;

class ExceptionHandler extends Handle
{
    private $code;
    private $msg;
    private $errorCode;
    //需要返回客户端当前路径的url

    public function render(Exception $e)
    {
        if($e instanceof BaseException){
            //如果是自定义的异常
            $this->code = $e->code;
            $this->msg = $e->msg;
            $this->errorCode = $e->errorCode;
        }else{
            //根据生产环境还是调试环境决定返回的数据形式
            if(config('app_debug')){
                return parent::render($e);
            }else{
                $this->code = 500;
                $this->msg = '服务器内部错误';
                $this->errorCode = 999;
                $this->recordErrorLog($e);
            }
        }
        $request=Request::instance();
        $res=[
            'msg' => $this->msg,
            'error_code' => $this->errorCode,
            'request_url'=>$request->url()
        ];
        return json($res,$this->code);
    }

    private function recordErrorLog(Exception $e){
        Log::init([
            // 日志记录方式，内置 file socket 支持扩展
            'type'  => 'File',
            // 日志保存目录
            'path'  => LOG_PATH.'error/',
            // 日志记录级别
            'level' => ['error'],
        ]);
        Log::record($e->getMessage(),'error');
    }
}