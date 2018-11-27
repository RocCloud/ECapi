<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/11/28
 * Time: 1:29
 */

namespace app\common\lib\delayqueue;

class DelayQueue
{

    private $prefix = 'delay_queue:';

    private $queue;

    private static $_instance = null;

    private function __construct($queue) {
        $this->queue = $queue;
    }

    final private function __clone() {}

    public static function getInstance($queue = '') {
        if(!self::$_instance) {
            self::$_instance = new DelayQueue($queue);
        }
        return self::$_instance;
    }

    /**
     * 添加任务信息到队列
     *
     * demo DelayQueue::getInstance('test')->addTask(
     *    'app\common\lib\delayqueue\job\Test',
     *    strtotime('2018-05-02 20:55:20'),
     *    ['abc'=>111]
     * );
     *
     * @param $jobClass
     * @param int $runTime 执行时间
     * @param array $args
     */
    public function addTask($jobClass, $runTime, $args = null)
    {

        $key = $this->prefix.$this->queue;

        $params = [
            'class' => $jobClass,
            'args'  => $args,
            'runtime' => $runTime,
        ];

        RedisHandler::getInstance()->zAdd(
            $key,
            $runTime,
            serialize($params)
        );
    }

    /**
     * 执行job
     * @return bool
     */
    public function perform()
    {
        $key = $this->prefix.$this->queue;
        //取出有序集第一个元素
        $result = RedisHandler::getInstance()->zRange($key, 0 ,0);

        if (!$result) {
            return false;
        }

        $jobInfo = unserialize($result[0]);

        print_r('job: '.$jobInfo['class'].' will run at: '. date('Y-m-d H:i:s',$jobInfo['runtime']).PHP_EOL);

        $jobClass = $jobInfo['class'];

        if(!@class_exists($jobClass)) {
            print_r($jobClass.' undefined'. PHP_EOL);
            RedisHandler::getInstance()->zRem($key, $result[0]);
            return false;
        }

        // 到时间执行
        if (time() >= $jobInfo['runtime']) {
            $job = new $jobClass;
            $job->setPayload($jobInfo['args']);
            $jobResult = $job->preform();
            if ($jobResult) {
                // 将任务移除
                RedisHandler::getInstance()->zRem($key, $result[0]);
                return true;
            }
        }

        return false;
    }

}
