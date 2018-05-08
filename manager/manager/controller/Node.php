<?php
namespace app\admin\controller;

use app\manager\model\NodeModel as NodeModel;
use think\Cache;
use think\Config;
use think\Controller;
use think\Request;

class node extends Base
{
    public function listing(){
        $allnodes = NodeModel::getAllNodes();
        $treelist = NodeModel::getTreeNode(0,$allnodes);
        $list = array();
        NodeModel::Tree2List($treelist,$list);
        $list = json_encode($list);
        $this->assign('list',$list);
        return $this->fetch();
    }

    public function editnode(){
        $nodelmodel = new NodeModel();
        $id = input('id');
        $model = $nodelmodel->where('id',$id)->find();

        if(request()->isAjax()){
            $updatemodel = array();
            $formdata = request()->post('form/a');
            $updatemodel['name'] = $formdata['name'];
            if($model['type']>0)
                $updatemodel['url'] = $formdata['url'];
            if($model['type']>1)
                $updatemodel['type'] = $formdata['type'];
            $updatemodel['isshow'] = $formdata['isshow'];
            $updatemodel['sort'] = $formdata['sort'];
            $updatemodel['icon'] = str_replace('&','',$formdata['icon']);
            $nodelmodel->update($updatemodel,array('id'=>$id));
            Cache::rm('allnodes');
            return JsonObj(true,'保存成功',url('node/listing'));
        }else{
            $this->assign('model',$model);
            return $this->fetch();
        }
    }

    public function delete(){
        $nodelmodel = new NodeModel();
        $id = request()->get('id/d');
        $model = NodeModel::where('id',$id)->find();
        if(!$model)
            return JsonObj(false,'参数错误');
        $nodelmodel->where('id',$id)->delete();
        Cache::rm('allnodes');
        return JsonObj(true,'删除成功');
    }

    public function addmodule(){
        $nodemodel = new NodeModel();
        if(request()->isAjax()){
            $formdata = request()->post('form/a');
            $this->addnode('addmodule',$formdata);
            return JsonObj(true,'添加成功',url('node/listing'));
        }else{
            return $this->fetch();
        }
    }
    public function addpage(){
        $nodemodel = new NodeModel();
        $id = input('id/d');
        $module = $nodemodel->where('id',$id)->find();
        if(!$module)
            return JsonObj(false,'参数错误',url('node/listing'));
        if($module['type'] != 0)
            return JsonObj(false,'参数错误',url('node/listing'));

        if(request()->isAjax()){
            $formdata = request()->post('form/a');
            return $this->addnode('addpage',$formdata,$id);
        }else{
            return $this->fetch();
        }
    }
    public function addaction(){
        $nodemodel = new NodeModel();
        $id = input('id/d');
        $module = $nodemodel->where('id',$id)->find();
        if(!$module)
            return JsonObj(false,'参数错误',url('node/listing'));
        if($module['type'] != 1)
            return JsonObj(false,'参数错误',url('node/listing'));

        if(request()->isAjax()){
            $formdata = request()->post('form/a');
            return $this->addnode('addaction',$formdata,$id);
        }else{
            return $this->fetch();
        }
    }
    public function addbtn(){
        $nodemodel = new NodeModel();
        $id = input('id/d');
        $module = $nodemodel->where('id',$id)->find();
        if(!$module)
            return JsonObj(false,'参数错误',url('node/listing'));
        if($module['type'] != 1)
            return JsonObj(false,'参数错误',url('node/listing'));

        if(request()->isAjax()){
            $formdata = request()->post('form/a');
            return $this->addnode('addbtn',$formdata,$id);
        }else{
            return $this->fetch();
        }
    }
    private function addnode($type='',$formdata,$id=0){
        if(empty($type))
            return JsonObj(false,'参数错误',url('node/listing'));
        $nodemodel = new NodeModel();
        $formdata['pid'] = $id;
        switch ($type)
        {
            case 'addmodule':
                $type = 0;
                break;
            case 'addpage':
                $type = 1;
                break;
            case 'addbtn':
                $type = 2;
                break;
            case 'addaction':
                $type = 3;
                break;
        }
        $formdata['icon'] = str_replace('&','',$formdata['icon']);
        $formdata['type'] = $type;
        $nodemodel->save($formdata);
        Cache::rm('allnodes');
        return JsonObj(true,'添加成功',url('node/listing'));
    }

}
