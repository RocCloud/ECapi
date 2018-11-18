<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 2018/9/21
 * Time: 16:39
 */

namespace app\api\validate;


class AddressNew extends BaseValidate
{
    protected $rule=[
        'name'=>'require|isNotEmpty',
        //'mobile'=>'require|isMobile',
        'mobile'=>'require|isNotEmpty',
        'province'=>'require|isNotEmpty',
        'city'=>'require|isNotEmpty',
        'county'=>'require|isNotEmpty',
        'detail'=>'require|isNotEmpty',
    ];

}