<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/9/21
 * Time: 16:39
 */

namespace app\api\validate;


class Count extends BaseValidate
{
    protected $rule=[
        'count'=>'isPostiveInteger|between:1,15',
    ];

    protected $message=[
        'count.isPostiveInteger'=>'count参数必须是正整数',
    ];


}