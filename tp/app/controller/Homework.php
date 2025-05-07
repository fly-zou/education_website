<?php

namespace app\controller;

use app\BaseController;
use app\model\User;
use app\model\Assignment;
use app\model\StudentAssisgnment;
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

class Homework extends BaseController
{
  public function GetHomework()
  {
    $user = User::where('id', Session::get('user_id')) -> find();
    $class_code = User::get_class_code();
    $identity = $user -> identity;
    if ($identity == 'teacher'){
      $homework = Assignment::where('class_code', $class_code)
        ->select();
      foreach($homework as $s_work){
        $num_str = !empty($s_work->completed) ? $s_work['completed'] : 0;
        $s_work->submittedCount  = $num_str;
      }
    }
    else{
      $homework = StudentAssisgnment::where('id', Session::get('user_id'))
        ->select();
    }
    return json([
      'code' => 200,
      'homework' => $homework
    ]);
  }

  public function CreateHomework()
  {
    return View::fetch('HomeWork/NewHomeWork');
  }

  public function SaveHomework(){
    $class_code = User::get_class_code();
    $homework = $this->request->param('homework');
    $content = json_encode($homework['questions'], JSON_UNESCAPED_UNICODE);
    $save_homework = Assignment::where('title', $homework['title'])
      ->where('class_code', $class_code)
      ->find();  
    if ($save_homework){
      if ($save_homework['action'] != 'save'){
        return json([
          'code'=> 201,
          'msg' => '作业已存在，换个标题'
        ]);
      }
      $updateResult = Assignment::where('title', $homework['title'])
      ->where('class_code', $class_code)
      ->update(['deadline' => $homework['deadline'], 'content' => $content, 'action' => $homework['action']]);
    }
    else{
      $assisgnment = new Assignment();
      $assisgnment -> title = $homework['title'];
      $assisgnment -> class_code = $class_code;
      $assisgnment -> deadline = $homework['deadline'];
      $assisgnment -> content = $content;
      $assisgnment -> action = $homework['action'];
      $assisgnment->save();
    }
    if ($homework['action'] == 'save'){
      return json([
        'code' => 200,
        'msg' => '保存成功'
      ]);
    }
    else{
      $publish_homework = homework::PublishHomework($homework, $class_code);
      if ($publish_homework){
        return json([
          'code' => 200,
          'msg' => '发布成功'
        ]);
      }
    }
  }

  public function PublishHomework($homework, $class_code){
    $student = User::where('class_code', $class_code)
      ->where('identity', 'student')
      ->select();
    foreach ($student as $std){
      $student_homework = new StudentAssisgnment();
      $student_homework -> title = $homework['title'];
      $student_homework -> class_code = $class_code;
      $student_homework -> id = $std['id'];
      $student_homework->save();
    }
    return true;
  }

  public function edit(){
    $title = input('title');
    $homework = Assignment::where('title', $title)
      ->where('class_code', User::get_class_code())
      ->find();
    if (!$homework['content']){
      $homework['content'] = '';
    }
    $content = json_decode($homework['content'], true);
    return View::fetch('HomeWork/EditHomework', ['content' => $content, 'title' => $title, 'deadline' => $homework['deadline']]);
  }

  public function DeleteHomework(){
    $title = input('title');
    $homework = Assignment::where('title', $title)
      ->where('class_code', User::get_class_code())
      ->delete();
    return json(['code' => 200, 'msg' => '删除' . $title . '成功']);
  }

  public function CancelHomework(){
    $title = input('title');
    $homework = Assignment::where('title', $title)
      ->where('class_code', User::get_class_code())
      ->update(['deadline' => '2024-01-12 21:26:3']);
    return json(['code' => 200, 'msg' => '取消作业成功']);
  }

  public function Detail(){
    $title = input('title');
    $homework = Assignment::where('title', $title)
      ->where('class_code', User::get_class_code())
      ->find();
    if (!$homework['content']){
      $homework['content'] = '';
    }
    $content = json_decode($homework['content'], true);
    return View::fetch('HomeWork/HomeworkDetail', ['content' => $content, 'title' => $title, 'deadline' => $homework['deadline']]);
  }

  public function StudentHomeworkList(){
    $title = input('title');
    $studentHomework = StudentAssisgnment::where('title', $title)
      ->where('class_code', User::get_class_code())
      ->where(function($query){
        $query -> where('action', 'submit')
          ->whereOr('action', 'corrected');
      })
      ->select();
    foreach($studentHomework as $homework){
      $student_name = User::where('id', $homework['id'])
        ->find();
      $homework['student_name'] = $student_name['username'] . '的作业';
    }
    return json([
      'code' => 200,
      'studentHomeworks' => $studentHomework
    ]);
  }

  public function STDHomework()
  {
    $studentHomework = StudentAssisgnment::where('class_code', User::get_class_code())
      ->where('id', Session::get('user_id')) 
      ->select();
    foreach($studentHomework as $homework){
      $teacher_homework = Assignment::where('class_code', $homework['class_code'])
        ->where('title', $homework['title'])
        ->find();
      $homework['deadline'] = $teacher_homework['deadline'];
      if (empty($homework['action'])){
        $homework['action'] = false;
      }
      if (empty($homework['correct'])){
        $homework['corrected'] = false;
      }
      else{
        $homework['corrected'] = true;
      }
      if (empty($homework['score'])){
        $homework['score'] = false;
      }
      else{
        $sum_score = 0;
        $give_score = json_decode($homework['score'], true);
        foreach($give_score as $score){
          $sum_score = $sum_score + $score;
        }
        $homework['score'] = $sum_score;
      }
      if ($homework['action'] == 'corrected'){
        $homework['action'] = 'submit';
      }
      unset($homework['answer']);
      unset($homework['class_code']);
      unset($homework['id']);
    }
    return json([
      'code' => 200,
      'assignments' => $studentHomework
    ]);
  }

  public function STDview()
  {
    $title = input('title');
    $homework = Assignment::where('title', $title)
      ->where('class_code', User::get_class_code())
      ->find();
    $std_homework = StudentAssisgnment::where('title', $title)
      ->where('id', Session::get('user_id'))
      ->where('class_code', User::get_class_code())
      ->find();
    $content = json_decode($homework['content'], true);
    if (!$std_homework['answer']){
      $answer = '[]';
    }
    else{
      $answer = json_decode($std_homework['answer'], true);
    }
    if (!$std_homework['score']){
      foreach($content as $con){
        $con['score'] = '';
      }
    }
    else{
      $result = [];
      $teacher_score = json_decode($std_homework['score'], true);
      foreach ($content as $index => $item){
        if (isset($teacher_score)){
          $item['score'] = $teacher_score[$index];
        }
        $result[] = $item;
      }
    }
    $content = $result;
    if (!$std_homework['correct']){
      foreach($content as $con){
        $con['comment'] = '';
      }
    }
    else{
      $teacher_correct = json_decode($std_homework['correct'], true); 
      $result = [];
      foreach ($content as $index => $item){
        if (isset($teacher_correct)){
          $item['comment'] = $teacher_correct[$index];
        }
        $result[] = $item;
      }
    }
    $content = $result;
    return View::fetch('HomeWork/STDHomeworkView', ['content' => $content, 'title' => $title, 'answers' => $answer]);
  }

  public function STDedit()
  {
    $title = input('title');
    $homework = Assignment::where('title', $title)
      ->where('class_code', User::get_class_code())
      ->find();
    $content = json_decode($homework['content'], true);
    $std_homework = StudentAssisgnment::where('title', $title)
      ->where('class_code', User::get_class_code())
      ->find();
    if (empty($std_homework['answer'])){
      $answers = '[]';
    }
    else{
      $answers = $std_homework['answer'];

    }
    $answers = json_decode($answers, true);
    return View::fetch('HomeWork/STDHomeworkEdit', ['content' => $content, 'title' => $title, 'deadline' => $homework['deadline'], 'answers' => $answers]);

  }
  
  public function STDSubmit()
  {
    $class_code = User::get_class_code();
    $payload = $this->request->param('STDAnswers');
    $answers = json_encode($payload['answers'], JSON_UNESCAPED_UNICODE);
    $title = $payload['title'];
    $std_homework = StudentAssisgnment::where('title', $title)
      ->where('id', Session::get('user_id'))
      ->find();
    if ($std_homework['action'] != 'submit' or $std_homework['action'] != 'corrected'){
      $action = $payload['action'];
    }
    if ($std_homework){
      $std_homework = StudentAssisgnment::where('title', $title)
        ->where('id', Session::get('user_id'))
        ->update(['answer' => $answers, 'action' => $action]);
    }
    $homework = Assignment::where('title', $title)
      ->where('class_code', $class_code)
      ->find();
    $completed_num = $homework['completed'];
    if (!$completed_num){
      $completed_num = $completed_num + 1;
    }
    else{
      $completed_num = 1;
    }
    $homework = Assignment::where('title', $title)
      ->where('class_code', $class_code)
      ->update(['completed' => $completed_num]);
    return json(['code' => 200]);
  }

  public function ShowStudentHomework()
  {
    $id = input('id');
    $title = input('title');
    $homework = Assignment::where('title', $title)
      ->where('class_code', User::get_class_code())
      ->find();
    $content = json_decode($homework['content'], true);
    $std_homework = StudentAssisgnment::where('title', $title)
      ->where('id', $id)
      ->where('class_code', User::get_class_code())
      ->find();
    if (empty($std_homework['answer'])){
      $answers = '[]';
    }
    else{
      $answers = $std_homework['answer'];
    }
    if (!empty($std_homework['score'])){
      $score = json_decode($std_homework['score'], true);
      $correct = json_decode($std_homework['correct'], true);
      foreach ($content as $index => &$item) {
          if (isset($score[$index])) {
              $item['score'] = $score[$index];
          }
          if (isset($correct[$index])) {
              $item['comment'] = $correct[$index];
          }
      }
    }
    $answers = json_decode($answers, true);
    return View::fetch('HomeWork/correctHomework', ['content' => $content, 'title' => $title, 'deadline' => $homework['deadline'], 'answers' => $answers, 'id' => $id]);
  }

  public function AIcorrectHomework()
  {
    $workdetail = $this->request->param('workdetail');
    $title = $workdetail['title'];
    $student_id = $workdetail['student_id'];
    $homework = Assignment::where('title', $title)
      ->where('class_code', User::get_class_code())
      ->find();
    $content = json_decode($homework['content'], true);
    $std_homework = StudentAssisgnment::where('title', $title)
      ->where('id', $student_id)
      ->where('class_code', User::get_class_code())
      ->find();
    $final_score = [];
    $final_comment = [];
    if (empty($std_homework['answer'])){
      foreach ($content as $question){
        $final_score[] = 0;
        $final_comment[] = '没有作答';
      }
    }
    else{
      $answers = $std_homework['answer'];
    }
    $answers = json_decode($answers, true);
    if (!empty($std_homework['score'])){
      $score = json_decode($std_homework['score'], true);
      $correct = json_decode($std_homework['correct'], true);
      foreach ($content as $index => &$item) {
          if (isset($score[$index])) {
              $item['score'] = $score[$index];
          }
          if (isset($correct[$index])) {
              $item['comment'] = $correct[$index];
          }
      }
    }
    $resp = [];
    foreach ($content as $index => &$question){
      $need_answer = '';
      $ask_question = '';
      $select = '';
      if ($question['type'] == 'single'){
        $need_answer = '你是一个作业批改器，你需要根据提供的单选题, 提供的正确答案和题目总分数对该学生的答案进行判断，并给出得分（错了0分，对了满分），如果该学生做错了(学生答案为空默认做错)，你需要指出学生的错误，并按照总分格式给出该题目的解法，如果该学生做对了，你要指出该题目的易错点在哪里，你将严格以{\"score\": ,\"comment\":}的形式返回你的评判\n';
        $ask_question = 'question:' . $question['content'] . '\n';
        $select = 'select：';
        $prefixLetter = 'A'; // 初始化字母前缀
        foreach ($question['options'] as $option) {
            $select .= $prefixLetter . ' ' . (string)$option . '|';
            $prefixLetter++; // 字母自动递增（A→B→C...）
        }
        $right_answer = 'righ_answer:' . $question['answer'] . '\n';
        $std_answer = 'student_answer: ';
        $student_answer = chr(ord('A') + (int)$answers[$index]);
        $std_answer = $std_answer . $student_answer;
        // 移除末尾多余的逗号（可选）
        $select = rtrim($select, ',');
        $sumscore = 'sumscore: ' . $question['sumscore'] . '\n';
        $final_question = $need_answer . $ask_question . $select . $right_answer . $std_answer . $sumscore;
      }
      else if($question['type'] == 'blank'){
        $need_answer = '你是一个作业批改器，你需要根据提供的问题，提供的正确答案和该题目的总分数对该学生的答案进行判断，并给出得分，如果该学生做错了(学生答案为空默认做错)，你需要指出学生的错误，并按照总分格式给出该题目的解法，如果该学生做对了，你要指出该题目的易错点在哪里，你将严格以{\"score\": ,\"comment\":}的形式返回你的评判\n';
        $ask_question = 'question: ' . $question['content'] . '\n';
        $right_answer = 'righ_answer: ' . $question['answer'] . '\n';
        $std_answer = 'student_answer: ' . $answers[$index] . '\n';
        $sumscore = 'sumscore: ' . $question['sumscore'] . '\n';
        $final_question = $need_answer . $ask_question . $right_answer . $std_answer . $sumscore;
      }
      else if($question['type'] == 'multiple'){
        $need_answer = '你是一个作业批改器，你需要根据提供的问题，提供的正确答案和该题目的总分数对该学生的答案进行判断，并给出得分，如果该学生做错了(学生答案为空默认做错)，你需要指出学生的错误，并按照总分格式给出该题目的解法，如果该学生做对了，你要指出该题目的易错点在哪里，你将严格以{\"score:\" ,\"comment\":}的形式返回你的评判\n';
        $ask_question = 'question:' . $question['content'] . '\n';
        $select = '';
        $prefixLetter = 'A'; // 初始化字母前缀
        foreach ($question['options'] as $option) {
            $select .= $prefixLetter . ' ' . (string)$option . '|';
            $prefixLetter++; // 字母自动递增（A→B→C...）
        }
        // 移除末尾多余的逗号（可选）
        $select = rtrim($select, ',');
        $right_answer = 'righ_answer:' . $question['answer'] . '\n';
        $std_answer = 'student_answer: ';
        if ($answers[$index] == ''){
          $give_score = 0;
        }
        else{
          $student_answer = explode(',' , $answers[$index]);
          foreach ($student_answer as $ans){
            $ans = chr((int)$ans + ord('A'));
            $std_answer = $std_answer . $ans . '|';
          }
          $std_answer = rtrim($std_answer, '|');
        }
        $sumscore = 'sumscore: ' . $question['sumscore'] . '\n';
        $final_question = $need_answer . $ask_question . $select . $right_answer . $std_answer . $sumscore;
      }
      $param = '{"message":{"content":{"type":"text","value":{"showText":"' . $final_question . '"';
      $param = $param . '}}},"source":"Tb844GlnqWoVKCTXVlM3HGq2IOynXxMD","from":"openapi","openId":"' . Session::get('user_id') . '"}';

      // 拼接 API 地址（含 appId 和 secretKey 查询参数）
      $api = 'https://agentapi.baidu.com/assistant/getAnswer';
      $queryParams = [
          'appId' => 'Tb844GlnqWoVKCTXVlM3HGq2IOynXxMD',
          'secretKey' => 'NIOrhb4CuHBQkEYj0778w24wUJFq0f46'
      ];
      $apiUrl = $api . '?' . http_build_query($queryParams);

      // 实例化 Guzzle 客户端
      $client = new Client([
          'timeout' => 30.0, // 超时时间
          'headers' => [
              'Content-Type' => 'application/json',
              'Accept' => 'application/json'
          ]
      ]);

      try {
          // 发送 POST 请求
          $response = $client->post($apiUrl, [
              'body' => $param
          ]);

          // 解析响应
          $statusCode = $response->getStatusCode();
          $responseBody = $response->getBody()->getContents();

          // 记录日志
          Log::info('API 请求参数：' . $param);
          Log::info('API 响应状态码：' . $statusCode);
          Log::info('API 响应内容：' . $responseBody);

          if ($statusCode == 200) {
              $data = json_decode($responseBody, true);
              if ($data['status'] == 0){
                $response = $data['data']['content'][0]['data'];
                $resp[] = $response;
              }
              else{
                $resp[] = $responseBody;
              }
          } else {
                $resp[] = $responseBody;
          }
      } catch (RequestException $e) {
          // 捕获 Guzzle 请求异常
          Log::info('Guzzle 请求异常：' . $e->getMessage());
          if ($e->hasResponse()) {
              $errorResponse = $e->getResponse()->getBody()->getContents();
              Log::info('错误响应内容：' . $errorResponse);
          }
          return json(['error' => '服务异常', 'message' => $e->getMessage()]);
      } catch (\Exception $e) {
          // 捕获其他异常
          Log::info('全局异常：' . $e->getMessage());
          return json(['error' => '服务异常', 'message' => $e->getMessage()]);
      } catch (\Throwable $e){
        Log::info($e);
        Log::error($e);
        return json(['error' => '服务异常', 'message' => $e->getMessage()]);
      }
    }
    foreach ($resp as $comm){
      $rep_answer = str_replace("json", "", $comm);
      $rep_answer = str_replace("```", "", $rep_answer);
      $decode_answer = json_decode($rep_answer, true);
      try{
        $final_comment[] = $decode_answer['comment'];
        $final_score[] = $decode_answer['score'];
      }
      catch (\Exception $e){
        Log::info($e);
        $final_score[] = -1;
        $final_comment[] = '出了些问题';
      }
    }
    return json([
      'code' => 200,
      'score' => $final_score,
      'comment' => $final_comment
    ]);
  }

  public function TeacherGrade()
  {
    $Grade = $this->request->param('teachergrade');
    $Score = [];
    $comment = [];
    foreach ($Grade['grading'] as $grade){
      $Score[] = $grade['score'];
      $comment[] = $grade['comment'];
    };
    $Score = json_encode($Score, JSON_UNESCAPED_UNICODE);
    $comment = json_encode($comment, JSON_UNESCAPED_UNICODE);
    Log::info(print_r($Score, true));
    Log::info(print_r($comment, true));
    $updateResult = StudentAssisgnment::where('title', $Grade['title'])
      ->where('id', (int)$Grade['student_id'])
      ->update(['score' => $Score, 'correct' => $comment, 'action' => 'corrected']);
    return json(['success' => 200]);

  }

  public function GenerateAnswers()
  {
    $answers = [];
    $questions = $this->request->param('questionsData')['questions'];
    foreach ($questions as $question){
      $need_answer = '';
      $ask_question = '';
      $select = '';
      Log::info(print_r($question, true));
      if ($question['type'] == 'single'){
        Log::info('single');
        $need_answer = '你是一个答题机器，你需要回答下面问题，并从选项中选出一个最符合题意的答案，严格以{\"answer\":}的形式返回答案，不需要理由\n';
        $ask_question = 'question:' . $question['content'] . '\n';
        $select = '';
        $prefixLetter = 'A'; // 初始化字母前缀
        foreach ($question['options'] as $option) {
            $select .= $prefixLetter . ' ' . (string)$option . '|';
            $prefixLetter++; // 字母自动递增（A→B→C...）
        }

        // 移除末尾多余的逗号（可选）
        $select = rtrim($select, ',');
        $final_question = $need_answer . $ask_question . $select;
      }
      else if($question['type'] == 'blank'){
        $need_answer = '你是一个答题机器，你需要回答下面问题，并严格以{\"answer\":}的形式返回答案，不需要理由\n';
        $ask_question = 'question:' . $question['content'] . '\n';
        $final_question = $need_answer . $ask_question;
      }
      else if($question['type'] == 'multiple'){
        Log::info('multiple');
        $need_answer = '你是一个答题机器，你需要回答下面问题，并从选项中选出所有符合题意的答案，将所有答案只以{\"answer\":[]}的形式返回，不需要理由\n';
        $ask_question = 'question:' . $question['content'] . '\n';
        $select = '';
        $prefixLetter = 'A'; // 初始化字母前缀
        foreach ($question['options'] as $option) {
            $select .= $prefixLetter . ' ' . (string)$option . '|';
            $prefixLetter++; // 字母自动递增（A→B→C...）
        }
        // 移除末尾多余的逗号（可选）
        $select = rtrim($select, ',');
        $final_question = $need_answer . $ask_question . $select;
      }
      $param = '{"message":{"content":{"type":"text","value":{"showText":"' . $final_question . '"';
      $param = $param . '}}},"source":"Tb844GlnqWoVKCTXVlM3HGq2IOynXxMD","from":"openapi","openId":"' . Session::get('user_id') . '"}';

      // 拼接 API 地址（含 appId 和 secretKey 查询参数）
      $api = 'https://agentapi.baidu.com/assistant/getAnswer';
      $queryParams = [
          'appId' => 'Tb844GlnqWoVKCTXVlM3HGq2IOynXxMD',
          'secretKey' => 'NIOrhb4CuHBQkEYj0778w24wUJFq0f46'
      ];
      $apiUrl = $api . '?' . http_build_query($queryParams);

      // 实例化 Guzzle 客户端
      $client = new Client([
          'timeout' => 10.0, // 超时时间
          'headers' => [
              'Content-Type' => 'application/json',
              'Accept' => 'application/json'
          ]
      ]);

      try {
          // 发送 POST 请求
          $response = $client->post($apiUrl, [
              'body' => $param
          ]);

          // 解析响应
          $statusCode = $response->getStatusCode();
          $responseBody = $response->getBody()->getContents();

          // 记录日志
          Log::info('API 请求参数：' . $param);
          Log::info('API 响应状态码：' . $statusCode);
          Log::info('API 响应内容：' . $responseBody);

          if ($statusCode == 200) {
              $data = json_decode($responseBody, true);
              if ($data['status'] == 0){
                $resp = $data['data']['content'][0]['data'];
                $answers[] = $resp;
              }
              else{
                $answers[] = $responseBody;
              }
          } else {
                $answers[] = $responseBody;
          }
      } catch (RequestException $e) {
          // 捕获 Guzzle 请求异常
          Log::info('Guzzle 请求异常：' . $e->getMessage());
          if ($e->hasResponse()) {
              $errorResponse = $e->getResponse()->getBody()->getContents();
              Log::info('错误响应内容：' . $errorResponse);
          }
          return json(['error' => '服务异常', 'message' => $e->getMessage()]);
      } catch (\Exception $e) {
          // 捕获其他异常
          Log::info('全局异常：' . $e->getMessage());
          return json(['error' => '服务异常', 'message' => $e->getMessage()]);
      } catch (\Throwable $e){
        Log::info($e);
        Log::error($e);
        return json(['error' => '服务异常', 'message' => $e->getMessage()]);
      }
    }
    $final_answer = [];
    foreach ($answers as $answer){
      $rep_answer = str_replace("json", "", $answer);
      $rep_answer = str_replace("```", "", $rep_answer);
      $decode_answer = json_decode($rep_answer, true);
      try{
        $final_answer[] = $decode_answer['answer'];
      }
      catch (\Exception $e){
        $final_answer[] = '出了些问题';
      }
    }
    return json([
      'code' => 200,
      'answers' => $final_answer
    ]);
  }
  
}
