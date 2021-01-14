<?php

namespace App\Extension;

use App\Extension\Exception\DingTalkHandler;
use App\Extension\MongodbClient\LogClient;
use EasySwoole\EasySwoole\Config;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\MongoDBHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SwiftMailerHandler;
use Monolog\Processor\ProcessIdProcessor;
use Monolog\Processor\UidProcessor;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class Logger
{

    public static function log($data = [], $msg = '调试信息', $code = 100)
    {
        $logger = new \Monolog\Logger('controller_logs');
        $client = LogClient::getInstance();
        $mongodbHandler = new MongoDBHandler($client, 'logs', 'controller_logs');
        $logger->pushHandler($mongodbHandler);

        $instance = Config::getInstance();
        $logDir = $instance->getConf('LOG_DIR');
        // StreamHandler_1
        $streamHander1 = new StreamHandler($logDir.'/controller_logs.log', \Monolog\Logger::DEBUG);
        // 设置日志格式为json
        $streamHander1->setFormatter(new JsonFormatter());
        // 入栈, 往 handler stack 里压入 StreamHandler 的实例
        $logger->pushHandler($streamHander1);

        $mailer = new Swift_Mailer((new Swift_SmtpTransport('smtp.exmail.qq.com', 25))->setUsername('server_warning@babybus.com')->setPassword('Warning4server'));
        $message = (new Swift_Message())->setFrom(['server_warning@babybus.com' => '服务器警告'])->setTo(['liucaihuang@babybus.com']);
        $message->setSubject('警告, 快点来看看这个情况.')->setBody('快点来看看这个情况, 需要快点处理一下.');
        $emailHandler = new SwiftMailerHandler($mailer, $message);
        $logger->pushHandler($emailHandler);

        //钉钉通知
        $dingTalkHandler = new DingTalkHandler();
        $logger->pushHandler($dingTalkHandler);

        $logger->pushProcessor(new UidProcessor());
        $logger->pushProcessor(new ProcessIdProcessor());

        if (!is_array($data)) {
            $data = [$data];
        }

        $logger->addRecord($code, $msg, $data);
    }


    public static function webLog($data = [], $msg = '调试信息', $code = 100)
    {
        $logger = new \Monolog\Logger('controller_logs');
        $client = LogClient::getInstance();
        $mongodbHandler = new MongoDBHandler($client, 'logs', 'controller_logs');
        $logger->pushHandler($mongodbHandler);
        $logger->pushProcessor(new UidProcessor());
        $logger->pushProcessor(new ProcessIdProcessor());
        if (!is_array($data)) {
            $data = [$data];
        }
        $logger->addRecord($code, $msg, $data);
    }
}