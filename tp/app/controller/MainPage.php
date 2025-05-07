<?php

namespace app\controller;

use app\BaseController;
use think\facade\View;
use think\facade\Session;
use app\model\User;
use think\facade\Log;

class MainPage extends BaseController
{
    public function index()
    {
        $pages = [
          '/home/AI' => 'AI助手',
          '/home/homework' => '作业',
          '/home/section' => '章节',
          '/home/exam' => '考试',
          '/home/discussion' => '讨论',
          '/home/resource' => '资源',
          '/home/learning_record' => '学习记录'
        ];
        $user_id = Session::get('user_id');
        $user_name = User::where('id', $user_id)->value('username');
        return View::fetch('MainPage/index', ['user_name' => $user_name, 'pages' => $pages]);
    }
    public function AI()
    { 
      return View::fetch('MainPage/AI');
    }
    public function homework()
    {
        $identity = User::get_identity();
        if ($identity == 'teacher'){
          return View::fetch('MainPage/homework');
        }
        else {
          Log::info('student');
          return View::fetch('MainPage/std_homework');
        }
    }
    public function section()
    {
        $identity = User::get_identity();
        if ($identity == 'teacher'){
          return View::fetch('MainPage/section');
        }
        else {
          return View::fetch('MainPage/std_section');
        }
    }
    public function exam()
    {
        $identity = User::get_identity();
        if ($identity == 'teacher'){
          return View::fetch('MainPage/exam');
        }
        else {
          return View::fetch('MainPage/std_exam');
        }
    }

    public function discussion()
    {
        return View::fetch('MainPage/discussion');
    }

    public function resource()
    {
        $identity = User::get_identity();
        if ($identity == 'teacher'){
          return View::fetch('MainPage/resource');
        }
        else {
          return View::fetch('MainPage/std_resource');
        }
    }
    public function learningRecord()
    {
        return View::fetch('MainPage/learningRecord');
    }
}
