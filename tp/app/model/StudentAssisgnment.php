<?php

namespace app\model;

use think\facade\Session;
use think\Model;

class StudentAssisgnment extends Model
{
    // 设置数据表名（如果表名不是 'user'）
    protected $table = 'StudentHomework';

    // 定义时间戳字段（根据需求可选）
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
}
