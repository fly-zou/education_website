<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route as thinkRoute;
use think\facade\Route;

// 登录相关路由
Route::get('auth/login', 'Auth/login');
Route::post('auth/checklogin', 'Auth/checkLogin');
Route::get('auth/logout', 'Auth/logout');
Route::get('auth/register', 'Auth/register');
Route::post('auth/doregister', 'Auth/doRegister');
//resource
Route::post('/home/resource/upload', 'Resource/upload');
Route::get('/home/resource/filelist', 'Resource/filelist');
Route::get('/home/download', 'Resource/download');
Route::get('/home/deletefile', 'Resource/deletefile');
//homework
Route::get('/home/homeworks/gethomework', 'Homework/GetHomework');
Route::get('/home/homeworks/createhomework', 'Homework/CreateHomework');
Route::post('/homeworks/savehomework', 'Homework/SaveHomework');
Route::get('/homework/edit', 'Homework/edit');
Route::get('/homework/deletehomework', 'Homework/DeleteHomework');
Route::get('/homework/cancelhomework', 'Homework/CancelHomework');
Route::get('/homework/detail', 'Homework/Detail');
Route::get('/homework/studenthomework', 'Homework/StudentHomeworkList');
Route::get('/homework/STDhomeworks', 'Homework/STDHomework');
Route::get('/homework/STDview', 'Homework/STDview');
Route::get('/homework/STDedit', 'Homework/STDedit');
Route::post('/homework/STDSubmit', 'Homework/STDSubmit');
Route::get('/homework/correct', 'Homework/ShowStudentHomework');
Route::post('/homework/TeacherGrade', 'Homework/TeacherGrade');
Route::post('/homework/GenerateAnswers', 'Homework/GenerateAnswers');
//section
Route::get('/section/create', 'Sections/create');
Route::get('/section/sectiondelete', 'Sections/SectionDelete');
Route::get('/section/changeName', 'Sections/change_name');
Route::get('/section/showSection', 'Sections/show_section');
Route::get('/section/taskDetail', 'Sections/task_detail');
Route::post('/sections/addTaskFile', 'Sections/AddTaskFile');
Route::get('/sections/streamvideo', 'Sections/streamVideo');
Route::get('/sections/DeleteTaskFile', 'Sections/DeleteTaskFile');
// 主要功能
Route::get('/', 'MainPage/index');
Route::get('/home/AI', 'MainPage/AI');
Route::get('/home/homework', 'MainPage/homework');
Route::get('/home/section', 'MainPage/section');
Route::get('/home/exam', 'MainPage/exam');
Route::get('/home/discussion', 'MainPage/discussion');
Route::get('/home/resource', 'MainPage/resource');
Route::get('/home/learning_record', 'MainPage/learningRecord');
//AI_access
Route::post('/home/AITalk', 'AIAccess/talk');
//errors
Route::get('/errors/NoClassCode', 'MyErrors/NoClassCode');
