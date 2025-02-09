<?php

namespace app\controller;

use app\BaseController;
use app\model\User;
use think\facade\Session;
use think\facade\View;

class Auth extends BaseController
{
    // 显示登录页面
    public function login()
    {
        return View::fetch('auth/login');
    }

    // 处理登录请求
    public function checkLogin()
    {
        $username = $this->request->param('username');
        $password = $this->request->param('password');

        // 查找用户
        $user = User::where('username', $username)->find();

        if ($user && password_verify($password, $user->password)) {
            // 登录成功，保存Session
            Session::set('user_id', $user->id);
            return redirect('/');
        } else {
            // 登录失败
            return View::fetch('auth/login', [
                'error' => '用户名或密码错误'
            ]);
        }
    }

    // 退出登录
    public function logout()
    {
        Session::delete('user_id');
        return redirect('/auth/login');
    }

    public function register()
    {
        return View::fetch('auth/register');
    }

    public function doRegister()
    {
        $username = $this->request->param('username');
        $password = password_hash($this->request->param('password'), PASSWORD_DEFAULT);
        $identity = $this->request->param('identity');
        $data_username = User::where('username', $username)->find();
        if ($data_username) {
            return View::fetch('auth/register', [
                'error' => '用户名已存在'
            ]);
        }
        $user = new User();
        $user->username = $username;
        $user->password = $password;
        $user->identity = $identity;
        $user->save();

        return redirect('/auth/login');
    }
}

class Index extends BaseController
{
    protected $middleware = ['auth'];

    public function index()
    {
        return '欢迎回来！';
    }
}
