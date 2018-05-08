<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/8
 * Time: 16:49
 */

namespace app\manager\controller;
use think\Controller;

class Permissions extends Controller{
    public function index(){
        echo $this->fetch();
    }
    public function add(){
        echo $this->fetch();
    }
    public function update(){
        echo $this->fetch();
    }
}