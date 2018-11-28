<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/11/28
 * Time: 1:32
 */

namespace app\common\lib\delayqueue\job;

use app\api\model\Order;
use app\common\lib\delayqueue\DelayJob;

class CloseOrder extends DelayJob
{
    public function preform()
    {
        // payload 里应该有处理任务所需的参数，通过DelayQueue的addTask传入
        $args = $this->payload;
        $order=Order::getOrderByID($args['order_id']);
        if($order->status == 1){
            $res = Order::PaymentDelay($args['order_id']);
            if ($res){
                Log::init([
                    'type'  => 'file',
                    // 日志保存目录
                    'path'  => LOG_PATH.'command/delayqueue/',
                    // 日志记录级别
                    'level' => [],
                ]);
                Log::record('order_id:'.$order->id .' status update success'."\r\n");
                return true;
            }
        }else{
            return true;
        }
    }
}
