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
        //print_r('test job'.PHP_EOL);
        $args = $this->payload;
        $res = Order::PaymentDelay($args['order_id']);
        if ($res){
            return true;
        }
    }
}
