<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/22
 * Time: 21:15
 */
namespace app\manager\model;

use think\Cache;
use think\Model;

class NodeModel extends Model
{
    //公共的节点，所有人都可以看到的
    public static $defaultnodes = array('控制面板','个人信息','运行环境');

    protected $table = 's_node';

    /**
     * 根据传入的节点列表，获取节点的树形结构
     * pid为根节点ID
     * list为传入的节点列表
     * showall是否显示隐藏节点
    */
    public static function getTreeNode($pid=0,$list,$showall=true){
        $tree = array();
        foreach($list as $v){
            if( !$showall && !$v['isshow'])
                continue;
            if($v['pid'] == $pid ){
                $v['children'] = self::getTreeNode($v['id'],$list,$showall);
                if( $v['children'] == null){
                    unset($v['children']);
                }
                $tree[] = $v;
            }
        }
        return $tree;
    }

    /**
     * 将树形节点转换成一般列表节点
     * 节点列表页面专用函数
    */
    public static function Tree2List($tree,&$list,$deepth=0){
        foreach($tree as $v){
            $children = array();
            if(isset($v['children']))
            {
                $children = $v['children'];
                unset($v['children']);
            }
            $pre = '';
            if($deepth>0){
                for($i=0;$i<$deepth;$i++)
                    $pre .='　　';
                $pre .= '|——';
            }
            $v['name'] = $pre.$v['name'];
            switch ($v['type']){
                case 0:
                    $v['type'] = '模块';
                    break;
                case 1:
                    $v['type'] = '页面';
                    break;
                case 2:
                    $v['type'] = '按钮';
                    break;
                case 3:
                    $v['type'] = '操作';
                    break;
            }
            $v['isshow'] = $v['isshow']?'显示':'隐藏';
            $list[] = $v;
            self::Tree2List($children,$list,$deepth+1);
        }
    }

    /**
     * 将树形节点转成一般列表节点
     * 授权页面专用函数
    */
    public static function GetAuthNodesList($tree,&$list,$deepth=0){
        foreach($tree as $v){
            $children = array();
            if(isset($v['children']))
            {
                $children = $v['children'];
                unset($v['children']);
            }
            $v['deepth'] = $deepth;
            if($v['type'] == 1)
            {
                //如果为页面类型，则找出页面下的按钮和操作，在页面上呈现出来，供授权选择使用
                $actions = array();
                foreach($children as $c)
                {
                    $actions[] = $c;
                }
                $v['actions'] = $actions;
            }else{
                $v['actions'] = array();
            }
            $list[] = $v;
            //如果是模块，则进行递归，往下级节点继续整理
            if($v['type']<1)
                self::GetAuthNodesList($children,$list,$deepth+1);
        }
    }

    /**
     * 获取所有节点
    */
    public static function getAllNodes(){
        $allnodes = Cache::get('allnodes');
        if(!$allnodes){
            $allnodes = self::order('sort','desc') ->select();
            $newnodes = array();
            foreach($allnodes as $k=>$v)
                $newnodes[$v['id']] = $v;
            $allnodes = $newnodes;
            Cache::set('allnodes',$allnodes,60);
        }
        return $allnodes;
    }

    /**
     * 根据Url地址和传入的节点列表，获取该页面的按钮和列表操作项
    */
    public static function getNodesInPage($url,$allnodes){
        $btns = $actions = array();
        foreach($allnodes as $v){
            if(key_exists($v['pid'],$allnodes) == false)
                continue;
            if( $allnodes[$v['pid']]['url'] == $url){
                if($v['type'] == 2)
                    $btns[$v['id']] = $v;
                else if($v['type'] == 3)
                    $actions[$v['id']] = $v;
            }
        }
        return array('btns'=>$btns,'actions'=>$actions);
    }

    //根据NodeID获取Node列表
    public static function getUserAuthedNodes($nodeidlist){
        $allnodes = self::getAllNodes();
        $nodelist = array();
        foreach($allnodes as $v){
            if( in_array($v['id'],$nodeidlist))
                $nodelist[$v['id']] = $v;
        }
        return $nodelist;
    }

}