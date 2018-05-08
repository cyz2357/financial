<?php
namespace app\manager\controller;

use app\manager\model\MapModel;
use app\manager\model\NodeModel;
use app\manager\model\RoleModel;
use think\Cache;
use think\Controller;
use app\manager\model\UserModel;
use app\manager\controller\Base;
class User extends Base
{
    public function index(){
        $usermodel = new UserModel();
        $model = $usermodel->getLoginUser();

        if(request()->isAjax()){
            $updatearray = array();
            $formdata = input('form/a');
            if(empty($formdata['upass']) == false)
                $updatearray['upass'] = md5( trim($formdata['upass']));
            $updatearray['nick'] = $formdata['nick'];
            $usermodel->update($updatearray,array('id'=>$model['id']));
            $model = $usermodel->where('id',$model['id'])->find();
            $usermodel->updateLoginUser($model);
            return JsonObj(true,'修改成功',url('user/index'));
        }
        else{
            $this->assign('model',$model);
            return $this->fetch();
        }
    }

    public function admins()
    {
        $usermodel = new UserModel();
        $pagesize = intval(input('pagesize'));
        $pagesize = $pagesize>0?$pagesize:20;
        $list = $usermodel->where('isadmin',1)->paginate($pagesize);
        $total = $list->total();
        $data = $list->items();

        foreach($data as $k=>$v){
            $data[$k]['last_login_time'] = date('Y-m-d H:i:s',$v['last_login_time']);
        }

        $this->assign('pagesize',$pagesize);
        $this->assign('curr',$list->currentPage());
        $this->assign('list',json_encode($data));
        $this->assign('total',$total);
        return $this->fetch();
    }

    public function adduser(){
        $usermodel = new UserModel();

        $model = array();
        if(request()->isAjax()){
            $formdata = input('form/a');
            if($formdata['uname'] == 'admin')
                return JsonObj(false,'admin为系统保留关键字');
            $exist = $usermodel->where('uname',trim($formdata['uname']))->find();
            if($exist)
                return JsonObj(false,'该用户名已注册');
            $formdata['upass'] = md5( trim($formdata['upass']));
            $usermodel->save($formdata);
            return JsonObj(true,'添加成功',url('user/userlist'));
        }else{
            $this->assign('model',$model);
            return $this->fetch();
        }
    }

    public function edituser(){
        $id = input('id/d');

        $usermodel = new UserModel();
        $user = $usermodel->where('id',$id)->find();

        if(!$user)
        {
            $this->error('参数错误',url('user/userlist'));
        }

        if(request()->isAjax()){
            $updatearray = array();
            $formdata = input('form/a');
            if(empty($formdata['upass']) == false)
                $updatearray['upass'] = md5( trim($formdata['upass']));
            $updatearray['nick'] = $formdata['nick'];
            if( $user['uname'] != 'admin'){
                $updatearray['status'] = $formdata['status'];
                $updatearray['isadmin'] = $formdata['isadmin'];

                if($user['isadmin'] && !$updatearray['isadmin'])
                {
                    //设置角色为0，即取消角色
                    $updatearray['role_id'] = 0;

                    //取消开通的特殊权限
                    $mapmodel = new MapModel();
                    $mapmodel->where('name','usernode')->where('key1',$user['id'])->delete();
                }
            }
            $usermodel->update($updatearray,array('id'=>$id));
            return JsonObj(true,'修改成功',url('user/userlist'));
        }
        else{
            $this->assign('model',$user);
            return $this->fetch();
        }
    }

    public function roles(){
        $rolemodel = new RoleModel();

        $list = $rolemodel->select();
        $list = json_encode($list);
        $this->assign('list',$list);
        return $this->fetch();
    }

    public function userlist()
    {
        $usermodel = new UserModel();
        $pagesize = intval(input('pagesize'));
        $pagesize = $pagesize>0?$pagesize:20;
        $list = $usermodel->where('status<2')->order('id','desc')->paginate($pagesize);
        $total = $list->total();
        $data = $list->items();

        foreach($data as $k=>$v){
            $data[$k]['last_login_time'] = date('Y-m-d H:i:s',$v['last_login_time']);
        }

        $this->assign('pagesize',$pagesize);
        $this->assign('curr',$list->currentPage());
        $this->assign('list',json_encode($data));
        $this->assign('total',$total);
        return $this->fetch();
    }

    public function deleteuser(){
        $id = input('id/d');
        $usermodel = new UserModel();
        $model = $usermodel->where('id',$id)->find();
        if(!$model)
        {
            return JsonObj(false,'参数错误',url('user/userlist'));
        }
        if($model['uname'] == 'admin')
        {
            return JsonObj(false,'不能删除管理员',url('user/userlist'));
        }
        $usermodel->update(array('status'=>2),array('id'=>$id));
        return JsonObj(true,'删除成功',url('user/userlist'));
    }

    public function addrole(){
        $rolemodel = new RoleModel();

        if(request()->isAjax()){
            $formdata = input('form/a');
            $exist = $rolemodel->where('name',$formdata['name'])->find();
            if($exist)
            {
                return JsonObj(false,'该名称已存在');
            }
            $rolemodel->save($formdata);
            return JsonObj(true,'添加成功',url('user/roles'));
        }
        else{
            return $this->fetch();
        }
    }

    public function editrole(){
        $id = input('id/d');

        $rolemodel = new RoleModel();
        $model = $rolemodel->where('id',$id)->find();
        if(!$model){
            $this->error('参数错误',url('user/roles'));
        }
        if(request()->isAjax()){
            $formdata = input('form/a');
            if( $model['name'] == '超级管理员' && $formdata['name']!='超级管理员')
            {
                return JsonObj(false,'超级管理员为系统保留关键字，禁止修改',url('user/roles'));
            }
            $exist = $rolemodel->where("name='".$formdata['name']."' and id!=".$id)->find();
            if($exist)
            {
                return JsonObj(false,'该名称已存在');
            }
            $rolemodel->update($formdata,array('id'=>$id));
            return JsonObj(true,'修改成功',url('user/roles'));
        }
        else{
            $this->assign('model',$model);
            return $this->fetch();
        }
    }

    public function deleterole(){
        $id = input('id/d');
        $nodemodel = new RoleModel();
        $model = $nodemodel->where('id',$id)->find();
        if(!$model)
        {
            return JsonObj(false,'参数错误',url('user/roles'));
        }
        if($model['name'] == '超级管理员')
        {
            return JsonObj(false,'不能删除超级管理员',url('user/roles'));
        }
        $nodemodel->where('id',2)->delete();
        return JsonObj(true,'删除成功',url('user/roles'));
    }

    public function setadminauth(){
        $id = input('id/d');
        $usermodel = new UserModel();
        $nodemodel = new NodeModel();

        $user = $usermodel->where('id',$id)->find();
        if(!$user){
            $this->error('参数错误',url('user/admins'));
        }

        if(request()->isAjax()){
            $formdata = input("form/a");
            $usermodel->setUserAuthNodes($user,$formdata);
            return JsonObj(true,'设置成功',url('user/admins'));
        }
        else{
            $list = array();
            $allnodes = NodeModel::getAllNodes();
            //将所有节点排列成行开始
            $treenodes = $nodemodel->getTreeNode(0,$allnodes);
            NodeModel::GetAuthNodesList($treenodes,$list,0);
            //将所有节点排列成行结束
            $authednodes = $usermodel->getAuthedNodes($user);

            $this->assign('authednodes',$authednodes);
            $this->assign('list',$list);
            return $this->fetch();
        }
    }

    public function setroleauth(){
        $id = input('id/d');
        $rolemodel = new RoleModel();
        $nodemodel = new NodeModel();

        $role = $rolemodel->where('id',$id)->find();
        if(!$role){
            $this->error('参数错误',url('user/roles'));
        }

        if(request()->isAjax()){
            $formdata = input("form/a");
            $rolemodel->setRoleAuthNodes($role,$formdata);
            return JsonObj(true,'设置成功',url('user/roles'));
        }
        else{
            $list = array();
            $allnodes = NodeModel::getAllNodes();
            //将所有节点排列成行开始
            $treenodes = $nodemodel->getTreeNode(0,$allnodes);
            NodeModel::GetAuthNodesList($treenodes,$list,0);
            //将所有节点排列成行结束
            $authednodes = $rolemodel->getAuthedNodes($role);

            $this->assign('authednodes',$authednodes);
            $this->assign('list',$list);
            return $this->fetch();
        }
    }

    public function setuserrole(){
        $id = input('id/d');

        $usermodel = new UserModel();
        $user = $usermodel->where('id',$id)->find();

        if(!$user)
        {
            $this->error('参数错误',url('user/admins'));
        }

        if(request()->isAjax()){
            if($user['uname'] == 'admin')
                return JsonObj(false,'admin的角色不能修改',url('user/admins'));
            $updatearray = array();
            $formdata = input('form/a');
            $updatearray['role_id'] = $formdata['roleid'];
            $usermodel->update($updatearray,array('id'=>$id));
            return JsonObj(true,'设置成功',url('user/admins'));
        }
        else{
            $rolemodel = new RoleModel();
            $roles = $rolemodel->select();
            $roles[] = array('id'=>0,'name'=>'无角色');

            $this->assign("roleid",$user['role_id']);
            $this->assign('roles',$roles);
            return $this->fetch();
        }

    }

    public function noauth(){
        return $this->fetch();
    }

}
