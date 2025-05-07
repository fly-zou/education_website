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
        $userid = $this->request->param('userid');
        $password = $this->request->param('password');

        // 查找用户
        $user = User::where('id', $userid)->find();

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
        $password = $this->request->param('password');
        $role = $this->request->param('role');
        $classCode = $this->request->param('classroom');

        // 验证学生必须填写班级代码
        if ($role == 2) {
          if (empty($classCode)){
            return json([
                'code' => 400,
                'msg' => '学生必须填写班级代码'
            ]);
          }
          else{
            $teacher = User::where('identity', 'teacher')
              ->where('class_code', $classCode)
              ->find();
            if (!$teacher){
              return json([
                'code' => 400,
                'msg' => '教室号填写错误'
              ]);
            }
          }
        }

        // 密码加密
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $user = new User();
        
        // 生成用户ID
        do {
            $userId = mt_rand(10000000, 99999999);
        } while (User::where('id', $userId)->find());
        
        // 教师生成班级代码
        if ($role == 1) {
            do {
                $classCode = mt_rand(10000, 99999);
            } while (User::where('class_code', $classCode)->find());
            $true_role = 'teacher';
        }
        else{
          $true_role = 'student';
        }

        try {
            $user->save([
                'id' => $userId,
                'username' => $username,
                'password' => $hashedPassword,
                'identity' => $true_role,
                'class_code' => $classCode,
            ]);
            
            // 返回JSON响应
            return json([
                'code' => 200,
                'user_id' => $userId,
                'msg' => '注册成功'
            ]);

        } catch (\Exception $e) {
            // 处理数据库异常
            return json([
                'code' => 500,
                'msg' => '注册失败：' . $e->getMessage()
            ]);
        }
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
