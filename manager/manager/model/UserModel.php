<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/22
 * Time: 21:15
 */
namespace app\manager\model;

use think\Model;

class UserModel extends Model
{
    protected $table = 's_user';

    public function login($formdata)
    {
        $uname = trim($formdata['uname']);
        $upass = trim($formdata['upass']);
        $data = array();
        $data['status'] = 0;
        $data['uname'] = $uname;
        if ($this->validateData($formdata, 'UserValid.login') != true) {
            $this->error = $this->getError();
            return false;
        }

        $user = self::where($data)->find();
        if (!$user) {
            $this->error = '用户不存在或被禁用！';
            return false;
        }
        // 密码校验
        if ( $upass != $user->upass ) {
            $this->error = '登陆密码错误！';
            return false;
        }
        // 更新登录信息
        $user->last_login_time = time();
        $user->last_login_ip   = getclientip();
        if ($user->save()) {
            // 执行登陆
            $loginmodel = $user;
            // 缓存登录信息
            session('admin_user', $loginmodel);
            return $user;
        }
        return false;
    }

    public function logout(){
        session(null);
    }

    public function getLoginUser(){
        $loginmodel = session('admin_user');

        if(!empty($loginmodel)){
            return $loginmodel;
        }
        $this->error = 'Session失效';
        return false;
    }

    public function updateLoginUser($user){
        session('admin_user', $user);
    }

    //获取用户已被授权的节点ID
    public function getAuthedNodes($user){
        $allauthnodes = array();

        $nodemodel = new NodeModel();
        $allnodes = $nodemodel->getAllNodes();
        if($user['uname'] == 'admin'){
            foreach($allnodes as $v)
                $allauthnodes[$v['id']] = $v;
            return $allauthnodes;
        }

        $rolemodel = new RoleModel();
        $role = $rolemodel->where('id',$user['role_id'])->find();
        if($role){
            if($role['name'] == '超级管理员'){
                foreach($allnodes as $v)
                    $allauthnodes[$v['id']] = $v;
                return $allauthnodes;
            }
        }

        foreach($allnodes as $k=>$v){
            if( in_array($v['name'],NodeModel::$defaultnodes) ){
                $allauthnodes[$v['id']] = $v;
            }
        }

        $mapmodel = new MapModel();
        $rolemap = $mapmodel->where('name','rolenode')->where('key1',$user['role_id'])->select();
        foreach($rolemap as $k=>$v){
            $allauthnodes[$v['key2']] = $allnodes[$v['key2']];
        }
        $usermap = $mapmodel->where('name','usernode')->where('key1',$user['id'])->select();
        foreach($usermap as $k=>$v)
        {
            $allauthnodes[$v['key2']] = $allnodes[$v['key2']];
        }
        return $allauthnodes;
    }

    //设置用户的特殊权限
    public function setUserAuthNodes($user,$nodes){
        if($user['uname'] == 'admin')
            return ;

        if(!is_array($nodes)){
            $nodes = array();
        }

        $nodemodel = new NodeModel();
        $allnodes = $nodemodel->getAllNodes();

        $default = array();
        foreach($allnodes as $v){
            if( in_array($v['name'],NodeModel::$defaultnodes) ){
                $default[] = $v['id'];
            }
        }

        $mapmodel = new MapModel();
        $rolemap = $mapmodel->where('name','rolenode')->where('key1',$user['role_id'])->select();
        $rolenodes = array();
        foreach($rolemap as $v)
        {
            $rolenodes[] = $v['key2'];
        }

        $nodes = array_diff($nodes,$rolenodes,$default);

        $mapmodel->where('name','usernode')->where('key1',$user['id'])->delete();
        foreach($nodes as $v){
            $data = array();
            $data['name'] = 'usernode';
            $data['key1'] = $user['id'];
            $data['key2'] = $v;
            $mapmodel->insert($data);
        }
    }

}