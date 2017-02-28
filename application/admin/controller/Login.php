<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;
use think\Session;

/**
 * Description of Login
 *
 * @author matt
 */
class Login extends Controller{
    public function index()
    {
        return view('login');
    }

    //put your code here
    public function dologin()
    {
        // 添加入库
        if (Request::instance()->isPost()) {
            $params = Request::instance()->param();
            
            $name = $params['name'];
            $password = $params['password'];
            
            $user = Db::table('user')->where('name', $name)->find();
            $val_password = md5(md5($password.'admin').'admin');
            if ($val_password != $user['password']) {
                header('Location: /admin/login');exit;
            }
            // 保存用户session
            Session::set('user', $user);
            
            header('Location: /admin/index');exit;
        } else {
            header('Location: /admin/login');exit;
        }
        
    }
    
    public function logout()
    {
        
        Session::delete('access');
        Session::delete('user');
        Session::flush();
        header('Location: /admin/login');exit;
    }
}
