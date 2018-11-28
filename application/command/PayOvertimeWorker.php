<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/11/28
 * Time: 1:24
 */

namespace app\command;


use app\api\model\Order as OrderModel;
use app\api\model\OrderProduct;
use app\api\model\Product as ProductModel;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use app\common\lib\payovertime\MyRedis;
use think\Db;
use think\Exception;
use think\Log;

class PayOvertimeWorker extends Command
{

    protected function configure()
    {
        $this->setName('pay-overtime')->setDescription('Pay Overtime');
    }

    //支付超时，执行回库
    protected function execute(Input $input, Output $output)
    {
        ini_set('default_socket_timeout', -1);  //不超时

        $redis = new  MyRedis();
        // 解决Redis客户端订阅时候超时情况
        $redis->setOption();
        //redis 过期事件监听函数
        $redis->psubscribe(array('__keyevent@0__:expired'), function ($redis, $pattern, $chan, $msg){
            Log::init([
                'type'  => 'file',
                // 日志保存目录
                'path'  => ROOT_PATH.'log/command/payovertime/',
                // 日志记录级别
                'level' => [],
             ]);
            // 回调函数,这里写处理逻辑
            Db::startTrans();
            try {
                $order = OrderModel::getOrderByID($msg);
                if ($order->status == 1) {
                    $res = OrderModel::PaymentDelay($msg);
                    if ($res) {
                        $proItems = OrderProduct::getDataByOrderID($msg);
                        foreach ($proItems as $item) {
                            ProductModel::where('id', '=', $item['product_id'])->setInc('stock', $item['count']);
                        }
                        Log::write('order_id:' . $order->id . ' status and products stock update success' . "\r\n", 'info');
                    } else {
                        Log::write('order_id:' . $order->id . ' status and products stock update failed' . "\r\n", 'info');
                    }
                }
                Db::commit();
            }catch (Exception $ex){
                Db::rollback();
                Log::write($ex,'error');
            }
        });
    }
}