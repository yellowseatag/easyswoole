<?php


namespace App\HttpController;

use App\Extension\Logger;
use EasySwoole\Http\AbstractInterface\Controller;


class Test extends Controller
{

    public function index() {
        //Logger::log(['text'=>'hello'], '测试异常', 400);
        //Logger::webLog(['text'=>'hello']);
    }

}