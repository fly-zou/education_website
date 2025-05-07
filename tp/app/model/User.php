<?php

namespace app\model;

use think\facade\Session;
use think\Model;

class User extends Model
{
    // 设置数据表名（如果表名不是 'user'）
    protected $table = 'users';

    // 定义时间戳字段（根据需求可选）
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    static public function get_class_code()
    {
      $user_id = Session::get("user_id");
      $user = User::find($user_id);
      $class_code = $user->class_code;
      return $class_code;
    }

    static public function get_identity(){
      $user_id = Session::get('user_id');
      $user = User::find($user_id);
      $identity = $user->identity;
      return $identity;
    }
}
