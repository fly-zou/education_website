<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route as thinkRoute;
use think\facade\Route;

// 登录相关路由
Route::get('auth/login', 'Auth/login');
Route::post('auth/checklogin', 'Auth/checkLogin');
Route::get('auth/logout', 'Auth/logout');
Route::get('auth/register', 'Auth/register');
Route::post('auth/doregister', 'Auth/doRegister');
// 主要功能
Route::get('/', 'MainPage/index');
Route::get('/home/AI', 'MainPage/AI');
Route::get('/home/homework', 'MainPage/homework');
Route::get('/home/section', 'MainPage/section');
Route::get('/home/exam', 'MainPage/exam');
Route::get('/home/discussion', 'MainPage/discussion');
Route::get('/home/resource', 'MainPage/resource');
Route::get('/home/learning_record', 'MainPage/learningRecord');
