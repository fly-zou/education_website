<?php

namespace app\controller;

use app\BaseController;
use app\model\User;
use think\facade\Session;
use think\facade\View;

class UseAI extends BaseController
{
  static public function WenXin($final_param){
    $param = '{"message":{"content":{"type":"text","value":{"showText":"' . $final_param . '"';
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
          return $resp;
        }
        else{
          return $responseBody;
        }
    } else {
          return $responseBody;
    }
  }
  static public function Doubao($final_param){
    $param = '{"message":{"content":{"type":"text","value":{"showText":"' . $final_param . '"';
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
          return $resp;
        }
        else{
          return $responseBody;
        }
    } else {
          return $responseBody;
    }
  }

}
