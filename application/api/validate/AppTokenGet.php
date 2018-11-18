<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/10/31
 * Time: 18:20
 */

namespace app\api\validate;


class AppTokenGet extends BaseValidate
{
    protected $rule = [
        'ac' => 'require|isNotEmpty',
        'se' => 'require|isNotEmpty'
    ];
}