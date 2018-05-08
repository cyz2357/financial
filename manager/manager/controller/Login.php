<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/8
 * Time: 9:27
 */
namespace app\manager\controller;
use think\Controller;
use think\Loader;
class Login extends Controller{
    public function login(){
        echo $this->fetch();
    }
    public function LoginSubmit(){
        include 'PasswordHash.php';
        $LoginName=input('LoginName');
        $LoginPassword=input('LoginPassword');
        $testHash = new \PasswordHash(8, false);
        $hashedPassword = $testHash->HashPassword($LoginPassword);
        $user=Db('user')->where('del',0)->where('account',$LoginName)->find();
        $bool= $testHash->CheckPassword($LoginPassword, $user['password']);  // false

        if($user){

            if($bool==true){
                getAlert('登录成功','/manager/index/index');
            }else{
                getAlert('登录失败','/manager/login/login');
            }
        }else{
            getAlert('登录失败','/manager/login/login');

        }

        }
        public function LogOut(){

        }
}