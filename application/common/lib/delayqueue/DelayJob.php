<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/11/28
 * Time: 1:30
 */

namespace app\common\lib\delayqueue;

class DelayJob
{

    protected $payload;

    public function preform ()
    {
        // todo
        return true;
    }


    public function setPayload($args = null)
    {
        $this->payload = $args;
    }

}
