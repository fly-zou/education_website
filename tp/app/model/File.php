<?php

namespace app\model;

use think\Model;

class File extends Model
{
    // 设置数据表名（如果表名不是 'user'）
    protected $table = 'File';

    // 定义时间戳字段（根据需求可选）
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    static public function deleteFile($filepath){
      if (!is_dir($path)) return false;

      $files = new RecursiveIteratorIterator(
          new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS),
          RecursiveIteratorIterator::CHILD_FIRST
      );

      foreach ($files as $file) {
          if ($file->isDir()) {
              rmdir($file->getRealPath());
          } else {
              unlink($file->getRealPath());
          }
      }

      rmdir($path);
    }
}
