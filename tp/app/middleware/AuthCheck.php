<?php

namespace app\middleware;

use think\facade\Session;
use think\facade\Request;
use think\facade\View;

class AuthCheck
{
    public function handle($request, \Closure $next)
    {
        // 获取当前请求的路径
        $path = Request::pathinfo();

        // 排除登录页面和注册页面的请求
        if (in_array($path, ['auth/login', 'auth/register', 'auth/doregister', 'auth/checklogin'])) {
            return $next($request);
        } elseif (!Session::has('user_id')) {
            return redirect('/auth/login');
        }
        return $next($request);
    }
}
