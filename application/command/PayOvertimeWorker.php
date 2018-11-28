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

class PayOvertimeWorker extends Command
{
    //const COMMAND_ARGV_1 = 'queue';

    protected function configure()
    {
        $this->setName('pay-overtime')->setDescription('Pay Overtime');
        //$this->addArgument(self::COMMAND_ARGV_1);
    }

    protected function execute(Input $input, Output $output)
    {
        ini_set('default_socket_timeout', -1);  //不超时
        $redis = new  MyRedis();
        // 解决Redis客户端订阅时候超时情况
        $redis->setOption();
        $redis->psubscribe(array('__keyevent@0__:expired'), function ($redis, $pattern, $chan, $msg){
            // 回调函数,这里写处理逻辑
            $order=OrderModel::getOrderByID($msg);
            print_r($order);
            if($order->status == 1){
                $res = OrderModel::PaymentDelay($msg);
                print_r($res);
                if ($res){
                    print_r('ok');
                }
            }else{
                print_r('ok');
            }
        });
    }
}