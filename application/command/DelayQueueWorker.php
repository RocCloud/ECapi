<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/11/28
 * Time: 1:24
 */

namespace app\command;


use app\common\lib\delayqueue\DelayQueue;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;

class DelayQueueWorker extends Command
{
    const COMMAND_ARGV_1 = 'queue';

    protected function configure()
    {
        $this->setName('delay-queue')->setDescription('延迟队列任务进程');
        $this->addArgument(self::COMMAND_ARGV_1);
    }

    protected function execute(Input $input, Output $output)
    {
        $queue = $input->getArgument(self::COMMAND_ARGV_1);
        //参数1 延迟队列表名,对应与redis的有序集key名
        while (true) {
            DelayQueue::getInstance($queue)->perform();
            usleep(300000);
        }
    }
}