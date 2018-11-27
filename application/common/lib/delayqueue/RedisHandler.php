<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/11/28
 * Time: 1:27
 */

namespace app\common\lib\delayqueue;


class RedisHandler
{
    public $provider;
    private static $_instance = null;

    private function __construct() {
        $this->provider = new \Redis();
        //host port
        $config = require_once 'config.php';
        $this->provider->connect($config['redis_host'], $config['redis_port']);
    }

    final private function __clone() {}

    public static function getInstance() {
        if(!self::$_instance) {
            self::$_instance = new RedisHandler();
        }
        return self::$_instance;
    }

    /**
     * @param string $key 有序集key
     * @param number $score 排序值
     * @param string $value 格式化的数据
     * @return int
     */
    public function zAdd($key, $score, $value)
    {
        return $this->provider->zAdd($key, $score, $value);
    }

    /**
     * 获取有序集数据
     * @param $key
     * @param $start
     * @param $end
     * @param null $withscores
     * @return array
     */
    public function zRange($key, $start, $end, $withscores = null)
    {
        return $this->provider->zRange($key, $start, $end, $withscores);
    }

    /**
     * 删除有序集数据
     * @param $key
     * @param $member
     * @return int
     */
    public function zRem($key,$member)
    {
        return $this->provider->zRem($key,$member);
    }

}
