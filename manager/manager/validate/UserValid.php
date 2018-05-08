<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/25 0025
 * Time: 下午 2:28
 */
namespace app\admin\validate;

use think\Validate;

class UserValid extends Validate
{
    //定义验证规则
    protected $rule = [
        'uname|用户名' => 'require|alphaNum|unique:user',
        'nick|昵称'       => 'require|unique:user',
        'upass|密码'  => 'requireWith:upass|length:32',
    ];

    //定义验证提示
    protected $message = [
        'uname.require' => '请输入用户名',
        'upass.require' => '密码不能为空',
        'upass.length'  => '密码长度不正确',
    ];

    //定义验证场景
    protected $scene = [
        //更新
        'update'  =>  ['uname', 'upass' => 'length:32'],
        //登录
        'login'  =>  ['uname'=>'require|token', 'upass'=>'require'],
    ];
}