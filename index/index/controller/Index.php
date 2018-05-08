<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/8
 * Time: 9:27
 */
namespace app\index\controller;
use think\Controller;
class Index extends Controller{
    public function index(){
        echo $this->fetch();
    }
}