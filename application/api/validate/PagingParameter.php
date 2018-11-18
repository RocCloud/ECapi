<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/10/6
 * Time: 22:06
 */

namespace app\api\validate;


class PagingParameter extends BaseValidate
{
    protected $rule = [
        'page' => 'isPostiveInteger',
        'size' => 'isPostiveInteger',
    ];

    protected $message = [
        'page' => '分页参数必须是正整数',
        'size' => '分页参数必须是正整数',
    ];
}