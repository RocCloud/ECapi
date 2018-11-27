<?php
use \app\api\service\MyRedis;
use \app\api\model\Order as OrderModel;

ini_set('default_socket_timeout', -1);  //不超时

$redis = new MyRedis();
// 解决Redis客户端订阅时候超时情况
$redis->setOption();
$redis->psubscribe(array('__keyevent@0__:expired'), 'keyCallback');
// 回调函数,处理逻辑
function keyCallback($redis, $pattern, $chan, $msg)
{
    OrderModel::PaymentDelay($msg);
}