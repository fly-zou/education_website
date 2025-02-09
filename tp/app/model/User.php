<?php

namespace app\model;

use think\Model;
{
    // 设置数据表名（如果表名不是 'user'）
    protected $table = 'users';

    // 定义时间戳字段（根据需求可选）
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
}
