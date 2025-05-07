<?php

namespace app\controller;

use app\BaseController;
use app\model\User;
use app\model\Assignment;
use app\model\StudentAssisgnment;
use app\model\Section;
use app\model\File;
use think\facade\Session;
use think\Validate;
use think\facade\Log;
use think\facade\Request;
use think\file\UploadedFile;
use think\Response;
use think\exception\HttpException;
use think\facade\View;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request as GuzzleRequest;

class Sections extends BaseController
{
  public function create()
  {
    $class_code = User::get_class_code();
    $IndexName = input('IndexName');
    $Serial = input('Serial');
    $ArraySerial = [];
    if (strpos($Serial, '.') !== false)
    {
      $ArraySerial = explode('.', $Serial);
      $ArraySerial = array_map('intval', $ArraySerial);
    }
    else{
      $ArraySerial[] = (int)$Serial;
      $ArraySerial[] = 0;
    }
    $isSerial = Section::where('first_index', $ArraySerial[0])
      ->where('last_index', $ArraySerial[1])
      ->where('class_code', $class_code)
      ->find();
    if ($isSerial)
    {
      return json(['code' => 201, 'msg' => '出了点小问题']);
    }
    $section = new Section();
    $section -> first_index = $ArraySerial[0];
    $section -> last_index = $ArraySerial[1];
    $section -> section_name = $IndexName;
    $section -> class_code = $class_code;
    $section -> save();
    return json(['code' => 200]);
  }

  public function SectionDelete()
  {
    $class_code = User::get_class_code();
    $IndexName = input('IndexName');
    $Serial = input('Serial');
    if (strpos($Serial, '.') !== false)
    {
      $ArraySerial = explode('.', $Serial);
      $ArraySerial = array_map('intval', $ArraySerial);
      $delete_task = Section::where('class_code', $class_code)
        ->where('first_index', $ArraySerial[0])
        ->where('last_index', $ArraySerial[1])
        ->delete();
      $FilePath = $_SERVER['DOCUMENT_ROOT'] . '/section_uploads/' . $class_code . '/' . $Serial;
      if (!is_dir($FilePath)){
        File::deleteFile($FilePath);
      }
      $LastSerial = $ArraySerial[1];
      do
      {
        $LastSerial += 1;
        $update_task = Section::where('class_code', $class_code)
          ->where('first_index', $ArraySerial[0])
          ->where('last_index', $LastSerial)
          ->update(['last_index' => $LastSerial - 1]);
      }while($update_task);
    }
    else{
      $FirstSerial = (int)$Serial;
      $delete_index = Section::where('class_code', $class_code)
        ->where('first_index', $FirstSerial)
        ->delete();
      $FilePath = $_SERVER['DOCUMENT_ROOT'] . '/section_uploads/' . $class_code;
      $folders = glob($FilePath . '/' . $Serial . '*', GLOB_ONLYDIR);
      foreach($folders as $folder){
        File::deleteFile($folder);
      }
      do{
        $FirstSerial += 1;
        $update_index = Section::where('class_code', $class_code)
          ->where('first_index', $FirstSerial)
          ->update(['first_index' => $FirstSerial - 1]);
      }while($update_index);
    }
    return json(['code' => 200, 'msg' => '删除成功']);
  }
  public function change_name()
  {
    $class_code = User::get_class_code();
    $IndexName = input('IndexName');
    $Serial = input('Serial');
    if (strpos($Serial, '.') !== false)
    {
      $ArraySerial = explode('.', $Serial);
      $ArraySerial = array_map('intval', $ArraySerial);
      $update_task = Section::where('class_code', $class_code)
        ->where('first_index', $ArraySerial[0])
        ->where('last_index', $ArraySerial[1])
        ->update(['section_name' => $IndexName]);
    }
    else{
      $update_index = Section::where('class_code', $class_code)
        ->where('first_index', $Serial)
        ->where('last_index', 0)
        ->update(['section_name' => $IndexName]);
    }
    return json(['code' => 200, 'msg' => '改名成功']);
  }
  public function show_section()
  {
    $class_code = User::get_class_code();
    $find_index = section::where('class_code', $class_code)
      ->select();
    $final_index = [];
    if (empty($find_index)){
      $find_index = [];
    }
    Log::info(print_r($find_index, true));
    foreach($find_index as $index){
      Log::info('exist');
      $need_index = [];
      if ($index['last_index'] == 0){
        $serial = (string)$index['first_index'];
      }
      else{
        $serial = (string)$index['first_index'] . '.' . (string)$index['last_index'];
      }
      $need_index[] = $serial;
      $need_index[] = $index['section_name'];
      $final_index[] = $need_index;
    }
    return json(['code' => 200, 'Index' => $final_index]);
  }

  public function task_detail()
  {
    $class_code = User::get_class_code();
    $IndexName = input('IndexName');
    $Serial = input('Serial');
    $ArraySerial = explode('.', $Serial);
    $ArraySerial = array_map('intval', $ArraySerial);
    $find_task = Section::where('class_code', $class_code)
      ->where('first_index', $ArraySerial[0])
      ->where('last_index', $ArraySerial[1])
      ->find();
    if (empty($find_task['file_address'])){
      return View::fetch('section/EmptySection', ['Serial' => $Serial]);
    }
    else{
      $Array_file = explode('.', $find_task['file_address']);
      if (end($Array_file) == 'mp4' or end($Array_file) == 'webm'){
        return View::fetch('section/VideoSection', ['Serial' => $Serial]);
      }
      else{
        return View::fetch('section/PDFSection', ['Serial' => $Serial]);
      }
    }
  }

  public function AddTaskFile()
  {
    $class_code = User::get_class_code();
    Log::info(print_r(Request::post(), true));
    $Serial = Request::post('serial');
    $ArraySerial = explode('.', (string)$Serial);
    $ArraySerial = array_map('intval', $ArraySerial);
    //upload_file = $this->request->param("")
    $files = Request::file('files');
    if (empty($files)){
      return json(['code' => 400, 'msg' => '未选择上传文件']);
    }
    $validate = new Validate();
    $validate->rule([
      'file' => 'fileSize:104857600000|fileExt:mp4,webm,pdf'
    ]);

    try {
      $savedFiles = [];
      $files = is_array($files) ? $files : [$files];
      foreach ($files as $file) {
        if (!$validate->check(['file' => $file])){
          throw new \Exception($validate->getError());
        }
        $savePath = $_SERVER['DOCUMENT_ROOT'] . '/section_uploads/' . $class_code . '/' . $Serial;
        if (!is_dir($savePath)){
          mkdir($savePath, 0777, true);
        }

        $orginalName = $file->getOriginalName();
        $file_size = $file->getSize();
        $filePath = $file->move($savePath, $orginalName);
        $update_task = Section::where('class_code', $class_code)
          ->where('first_index', $ArraySerial[0])
          ->where('last_index', $ArraySerial[1])
          ->update(['file_address' => $savePath . '/' . $orginalName]);
      }
      return json([
        'code' => 200,
        'msg' => '上传成功'
      ]);
    } catch (\Exception $e) {
      Log::error('文件上传失败:' . $e->getMessage());
      return json(['code' => 500, 'msg' => $e->getMessage()]);
    }
  }

  public function DeleteTaskFile()
  {
    $addfile = $this->request->param('newfile');
  }

  public function streamVideo()
  {
    $Serial = input('serial');
    $ArraySerial = explode('.', $Serial);
    $ArraySerial = array_map('intval', $ArraySerial);
    $class_code = User::get_class_code();
    $section_find = Section::where('class_code', $class_code)
      ->where('first_index', $ArraySerial[0])
      ->where('last_index', $ArraySerial[1])
      ->find();
    $videoPath = $section_find['file_address'];
    if (!file_exists($videoPath)) {
        return json(['error' => '视频不存在'], 404);
    }

    $fp = fopen($videoPath, 'rb');
    if (!$fp) {
        return json(['error' => '无法打开视频文件'], 500);
    }

    $size = filesize($videoPath);
    $bufferSize = 1024 * 1024; // 1MB缓冲区
    $start = 0;
    $end = $size - 1;

    // 处理Range请求
    if (isset($_SERVER['HTTP_RANGE'])) {
        $range = $_SERVER['HTTP_RANGE'];
        // 格式：bytes=0-1023
        list($unit, $rangeStr) = explode('=', $range);
        if ($unit == 'bytes') {
            list($startStr, $endStr) = explode('-', $rangeStr);
            $start = intval($startStr);
            if ($endStr !== '') {
                $end = intval($endStr);
            }
            // 计算内容长度
            $length = $end - $start + 1;

            // 设置响应头
            header('HTTP/1.1 206 Partial Content');
            header("Content-Range: bytes $start-$end/$size");
        }
    } else {
        $length = $size;
    }

    // 设置内容类型
    header('Content-Type: video/mp4');
    header('Accept-Ranges: bytes');
    header("Content-Length: " . ($end - $start + 1));

    // 移动指针到开始位置
    fseek($fp, $start);

    // 逐块读取输出
    $bytesSent = 0;
    while ($bytesSent < $length && !feof($fp)) {
        $chunkSize = min($bufferSize, $length - $bytesSent);
        echo fread($fp, $chunkSize);
        flush(); // 立即输出
        $bytesSent += $chunkSize;
    }

    fclose($fp);
  }

  public function ShowTask()
  {
    $Serial = input('serial');

    return True;
  }
  public function AnalysisPDF()
  {
    return True;
  }
}
