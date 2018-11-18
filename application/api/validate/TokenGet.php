<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/9/21
 * Time: 16:39
 */

namespace app\api\validate;


class TokenGet extends BaseValidate
{
    protected $rule=[
        'code'=>'require|isNotEmpty',
    ];

    protected $message=[
        'code'=>'未检测到code码',
    ];


}