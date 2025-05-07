<?php

namespace app\controller;

use app\BaseController;
use app\model\User;
use think\facade\Session;
use think\facade\View;

class MyErrors extends BaseController
{
    public function NoClassCode(){
      return View::fetch('errors/no_class_code');
    }
}
