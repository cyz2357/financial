<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/8
 * Time: 15:42
 */

namespace app\manager\controller;

use think\Controller;
class Index extends  Controller{
    public function index(){
        echo $this->fetch();
    }
    public function trade (){
        echo $this->fetch();
    }
}