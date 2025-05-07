<?php

namespace app\controller;

use app\BaseController;
use think\facade\Session;
use think\facade\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;

class AIAccess extends BaseController
{
    public function talk()
    {
        $user_talk = $this->request->param('user_talk');
        if (empty($user_talk)) {
            return json(['error' => 'user_talk is empty']);
        }

        try {
            // 构造请求参数（与 curl 一致）
            $param = '{"message":{"content":{"type":"text","value":{"showText":"' . $user_talk . '"';
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
                  return json([
                    'resp' => $resp
                  ]);
                }
                else{
                  return json([
                      'error' => 'API 调用失败',
                      'code' => $statusCode,
                      'detail' => $responseBody
                  ]);
                }
            } else {
                return json([
                    'error' => 'API 调用失败',
                    'code' => $statusCode,
                    'detail' => $responseBody
                ]);
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
}
