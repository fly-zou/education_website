<?php

namespace app\controller;

use app\BaseController;
use app\model\User;
use think\facade\Session;
use think\facade\View;
use think\facade\Http;

class AI_access extends BaseController
{
    public function talk($person_str)
    {
        $api = 'https://agentapi.baidu.com/assistant/getAnswer?appId=Tb844GlnqWoVKCTXVlM3HGq2IOynXxMD&secretKey=NIOrhb4CuHBQkEYj0778w24wUJFq0f46';
        $param = [
          "message" => [
            "content" => [
              "type" => "text",
              "value" => ["showText" => $person_str]
            ]
          ],
          "source" => "Tb844GlnqWoVKCTXVlM3HGq2IOynXxMD",
          "from" => "openapi",
          "openId" => Session::get('user_id')
        ];
        $response = Http::post($api, $param);
        return $response;
    }
}
