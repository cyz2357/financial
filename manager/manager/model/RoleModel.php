<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/22
 * Time: 21:15
 */
namespace app\manager\model;

use think\Model;

class RoleModel extends Model
{
    protected $table = 's_role';

    /**
     * 根据角色获取已授权的节点ID
    */
    public function getAuthedNodes($role){
        $allauthnodes = array();

        $nodemodel = new NodeModel();
        $allnodes = $nodemodel->getAllNodes();

        //如果是超级管理员，则返回所有节点
        if($role['name'] == '超级管理员'){
            foreach($allnodes as $v)
                $allauthnodes[] = $v['id'];
            return $allauthnodes;
        }

        //先将公共节点加入授权节点列表
        foreach($allnodes as $k=>$v){
            if( in_array($v['name'], NodeModel::$defaultnodes) ){
                $allauthnodes[$v['id']] = $allnodes[$v['id']];
            }
        }

        //查对应表，根据角色ID找到已授权的节点，加入节点列表
        $mapmodel = new MapModel();
        $rolemap = $mapmodel->where('name','rolenode')->where('key1',$role['id'])->select();
        foreach($rolemap as $k=>$v){
            $allauthnodes[$v['key2']] = $allnodes[$v['key2']];
        }
        return $allauthnodes;
    }

    /**
     * 根据传入的节点ID，设置该角色的授权
    */
    public function setRoleAuthNodes($role,$nodes){
        if($role['name'] == '超级管理员')
            return ;
        if(!is_array($nodes)){
            $nodes = array();
        }

        $nodemodel = new NodeModel();
        $allnodes = $nodemodel->getAllNodes();

        ##将公共节点从ID列表中去除--开始
        $default = array();
        foreach($allnodes as $v){
            if( in_array($v['name'],NodeModel::$defaultnodes) ){
                $default[] = $v['id'];
            }
        }
        $nodes = array_diff($nodes,$default);
        ##将公共节点从ID列表中去除--结束

        ##删除对应表中角色拥有的授权ID，加入新的节点ID
        $mapmodel = new MapModel();
        $mapmodel->where('name','rolenode')->where('key1',$role['id'])->delete();
        foreach($nodes as $v){
            $data = array();
            $data['name'] = 'rolenode';
            $data['key1'] = $role['id'];
            $data['key2'] = $v;
            $mapmodel->insert($data);
        }
    }


}