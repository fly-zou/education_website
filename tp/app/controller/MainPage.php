<?php

namespace app\controller;

use app\BaseController;
use app\model\home;
use think\facade\View;
use think\facade\Session;
use app\model\User;

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
        return View::fetch('MainPage/homework');
    }
    public function section()
    {
        return View::fetch('MainPage/section');
    }
    public function exam()
    {
        return View::fetch('MainPage/exam');
    }
    public function discussion()
    {
        return View::fetch('MainPage/discussion');
    }
    public function resource()
    {
        return View::fetch('MainPage/resource');
    }
    public function learningRecord()
    {
        return View::fetch('MainPage/learningRecord');
    }
}
