<?php
namespace app\admin\controller;

use app\manager\model\NodeModel;
use app\manager\model\UserModel;
use think\Cache;
use think\Config;
use think\config\driver\Json;
use think\Controller;

class Publics extends Controller
{
    protected function _initialize()
    {
    }

    public function index(){
        if(request()->isAjax()){
            $usermodel = new UserModel();
            $formdata = input('post.');
            $loginmodel = $usermodel->login($formdata);

            $data = array();
            if($loginmodel){
                $data['code'] = true;
                $data['msg'] = '登录成功';
                $data['url'] = url('user/index');
            }else{
                $data['code'] = false;
                $data['msg'] = $usermodel->getError();
                $data['url'] = '';
            }
            return json($data);
        }else{
            $usermodel = new UserModel();
            if($usermodel->getLoginUser())
            {
                $this->redirect(url('user/index'));
            }else{
                return $this->fetch();
            }
        }
    }

    public function loginout(){
        $usermodel = new UserModel();
        $usermodel->logout();
        return JsonObj(true,'退出登录',url('publics/index'));
    }

}
