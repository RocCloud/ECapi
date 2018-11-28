<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/11/28
 * Time: 1:24
 */

namespace app\command;


use app\api\model\Order as OrderModel;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use app\common\lib\payovertime\MyRedis;
use think\Log;

class PayOvertimeWorker extends Command
{

    protected function configure()
    {
        $this->setName('pay-overtime')->setDescription('Pay Overtime');
    }

    protected function execute(Input $input, Output $output)
    {
        ini_set('default_socket_timeout', -1);  //不超时

        $redis = new  MyRedis();
        // 解决Redis客户端订阅时候超时情况
        $redis->setOption();
        $redis->psubscribe(array('__keyevent@0__:expired'), function ($redis, $pattern, $chan, $msg){
            Log::init([
                'type'  => 'file',
                // 日志保存目录
                'path'  => LOG_PATH.'command/payovertime/',
                // 日志记录级别
                'level' => [],
             ]);
            // 回调函数,这里写处理逻辑
            $order=OrderModel::getOrderByID($msg);
            if($order->status == 1){
                $res = OrderModel::PaymentDelay($msg);
                if ($res){
                    Log::record('order_id:'.$order->id .' status update success'."\r\n");
                }else{
                    Log::record('order_id:'.$order->id .' status update failed'."\r\n");
                }
            }
        });
    }
}