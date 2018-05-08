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

       $bool= $testHash->CheckPassword($hashedPassword, $user['password']);  // false
        //校验密码，输出false

        if($user&&$bool==true){
            
        }




        }
}