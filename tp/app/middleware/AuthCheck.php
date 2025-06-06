<?php

namespace app\middleware;

use think\facade\Session;
use think\facade\Request;
use think\facade\View;
use app\model\User;
use think\facade\Log;

class AuthCheck
{
    public function handle($request, \Closure $next)
    {
        // 获取当前请求的路径
        $path = Request::pathinfo();
        Log::info($path);

        // 排除登录页面和注册页面的请求
        if (in_array($path, ['auth/login', 'auth/register', 'auth/doregister', 'auth/checklogin'])) {
            return $next($request);
        } elseif (!Session::has('user_id')) {
            return redirect('/auth/login');
        }

        //$user_id = Session::get('user_id');
        //$user = User::find($user_id);
        //$class_code = $user->class_code;
        $class_code = User::get_class_code();
        if (!$class_code) {
          if (in_array($path, ['', 'home/AItalk', 'home/AI'])){
            return $next($request);
          }
          else {
            return redirect('/errors/NoClassCode');
          }
        }
        return $next($request);
    }

}
