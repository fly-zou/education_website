<?php

namespace app\controller;

use app\BaseController;
use app\model\User;
use app\model\File;
use think\facade\Session;
use think\Validate;
use think\facade\Log;
use think\facade\Request;
use think\file\UploadedFile;
use think\Response;
use think\exception\HttpException;

class Resource extends BaseController
{
  public function upload()
  {
    $class_code = User::get_class_code();
    //upload_file = $this->request->param("")
    $files = Request::file('files');
    if (empty($files)){
      return json(['code' => 400, 'msg' => '未选择上传文件']);
    }
    $validate = new Validate();
    $validate->rule([
      'file' => 'fileSize:10485760|fileExt:jpg,png,pdf,doc,docx,mp4'
    ]);

    try {
      $savedFiles = [];
      $files = is_array($files) ? $files : [$files];
      foreach ($files as $file) {
        if (!$validate->check(['file' => $file])){
          throw new \Exception($validate->getError());
        }
        $savePath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $class_code;
        if (!is_dir($savePath)){
          mkdir($savePath, 0777, true);
        }

        $orginalName = $file->getOriginalName();
        $file_size = $file->getSize();
        $filePath = $file->move($savePath, $orginalName);
        $savedFiles[] = [
          'name' => $orginalName,
          'size' => $file_size,
          'path' => $savePath
        ];
        //Log::info((string)$savedFiles);
        $existingFile = File::where('file_name', $orginalName)
          ->where('class_code', $class_code)
          ->find();
        if ($existingFile){
          File::where('file_name', $existingFile->file_name)
            ->where('class_code', $existingFile->class_code)
            ->update(['file_size' => $file_size]);
        }
        else {
          $save_file = new File();
          $save_file->file_name = $orginalName;
          $save_file->file_size = $file_size;
          $save_file->class_code = $class_code;
          $save_file->file_address = $savePath;
          $save_file->save();
        }
      }
      Log::info('文件上传成功:' . json_encode($savedFiles));
      return json([
        'code' => 200,
        'msg' => '上传成功',
        'data' => $savedFiles
      ]);
    } catch (\Exception $e) {
      Log::error('文件上传失败:' . $e->getMessage());
      return json(['code' => 500, 'msg' => $e->getMessage()]);
    }
  }

  public function filelist(){
    try{
      $return_file = [];
      $class_code = User::get_class_code();
      $file_list = File::where('class_code', $class_code)
        ->select();
      foreach ($file_list as $file){
        $return_file[] = [
          'name' => $file->file_name,
          'size' => $file->file_size,
          'update_time' => $file->update_time
        ];
      }
    } catch (\Exception $e){
      Log::error($e->getMessage());
    }
    return json(
      [
        'code' => 200,
        'data' => $return_file
      ]
    );
  }

  public function download(){
    $class_code = User::get_class_code();
    try {
      $filename = input('filename');
      if (empty($filename)){
        throw new HttpException(400, '文件名不能为空');
      }
      if (preg_match('/\.\.\/|\.\.\\\/', $filename)){
        throw new HttpException(403, '非法文件名');
      }

      $file = File::where('class_code', $class_code)
        ->where('file_name', $filename)
        ->find();
      $file_path = $file->file_address . '/' . $filename;
      Log::info($file_path . '/' . $filename);
      if (!file_exists($file_path)){
        throw new HttpException(404, '文件不存在');
      }
      return Response::create($file_path, 'file')->name($filename);

    }
    catch (\Exception $e){
      Log::error($e->getMessage());
    }
  }

  public function deletefile(){
    try {
      $class_code = User::get_class_code();
      $filename = input('filename');
      Log::info('delete');
      if (empty($filename)){
        throw new HttpException(400, '文件名不能为空');
      }
      if (preg_match('/\.\.\/|\.\.\\\/', $filename)){
        throw new HttpException(403, '非法文件名');
      }

      $file = File::where('class_code', $class_code)
        ->where('file_name', $filename)
        ->find();
      $file_path = $file->file_address . '/' . $filename;
      if (!file_exists($file_path)){
        throw new HttpException(404, '文件不存在');
      }
      File::where('class_code', $class_code)
        ->where('file_name', $filename)
        ->delete();
      return json(['code' => 200, 'msg' => '删除成功']);
    }
    catch (\Exception $e){
      Log::error($e->getMessage());
    }
  }
}
