<?php

namespace app\model;

use think\Model;
include __DIR__ . '/../../vendor/autoload.php';

class Section extends Model
{
    // 设置数据表名（如果表名不是 'user'）
    protected $table = 'Section';

    // 定义时间戳字段（根据需求可选）
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';

    static public function pdftohtml($pdf_address){
      // 设置 pdftohtml 的二进制文件路径
      \Gufy\PdfToHtml\Config::set('pdftohtml.bin', '/opt/homebrew/bin/pdftohtml');
      // 设置 pdfinfo 的二进制文件路径
      \Gufy\PdfToHtml\Config::set('pdfinfo.bin', '/opt/homebrew/bin/pdfinfo');
      // 初始化
      $pdf = new Gufy\PdfToHtml\Pdf($pdf_address);
      // 转换为 HTML 并返回 [Dom 对象](https://github.com/paquettg/php-html-parser)
      $html = $pdf->html();
      return $html;
    }
}
