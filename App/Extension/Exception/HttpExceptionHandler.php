<?php
/**
 * Created by PhpStorm.
 * User: breaux liucaihuang@babybus.com
 * Date: 2020/2/11
 * Time: 09:17
 */

namespace App\Extension\Exception;

use App\Extension\MongodbClient\LogClient;
use EasySwoole\EasySwoole\Config;
use EasySwoole\EasySwoole\Trigger;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use MongoDB\Client;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\MongoDBHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Exception;
use App\Extension\Exception\DingTalkHandler;
use Monolog\Processor\ProcessIdProcessor;
use Monolog\Processor\UidProcessor;
use Monolog\Handler\SwiftMailerHandler;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class HttpExceptionHandler
{

    public static function handle( \Throwable $throwable, Request $request, Response $response )
    {
        //客户端返回
        $response->withHeader('Content-type','application/json;charset=utf-8');
        $response->withStatus(200);

        $status = method_exists($throwable, 'getMsgCode') ? $throwable->getMsgCode() : '0';
        $errorLog = method_exists($throwable, 'getErrorFlag') ? $throwable->getErrorFlag() : true;
        $data = [
            'status' => $status,
            'message' => $throwable->getMessage(),
            'data' => ''
        ];
        $response->write(json_encode($data));

        if ($errorLog) {
            //错误日志记录
            $logger = new Logger('server_warning');

            try{
                //写入MongoDB
                $client = LogClient::getInstance();
                $mongodbHandler = new MongoDBHandler($client, 'logs', 'server_warning');
                $logger->pushHandler($mongodbHandler);

                $instance = Config::getInstance();
                $logDir = $instance->getConf('LOG_DIR');
                // StreamHandler_1
                $streamHander1 = new StreamHandler($logDir.'/controller_logs.log', \Monolog\Logger::DEBUG);
                // 设置日志格式为json
                $streamHander1->setFormatter(new JsonFormatter());
                // 入栈, 往 handler stack 里压入 StreamHandler 的实例
                $logger->pushHandler($streamHander1);

//            $mailer = new Swift_Mailer((new Swift_SmtpTransport('smtp.exmail.qq.com', 25))->setUsername('server_warning@babybus.com')->setPassword('Warning4server'));
//            $message = (new Swift_Message())->setFrom(['server_warning@babybus.com' => '服务器警告'])->setTo(['liucaihuang@babybus.com']);
//            $message->setSubject('警告, 快点来看看这个情况.')->setBody('快点来看看这个情况, 需要快点处理一下.');
//            $emailHandler = new SwiftMailerHandler($mailer, $message);
//            $logger->pushHandler($emailHandler);
//
                //钉钉通知
                $dingTalkHandler = new DingTalkHandler();
                $logger->pushHandler($dingTalkHandler);

                $logger->pushProcessor(new UidProcessor());
                $logger->pushProcessor(new ProcessIdProcessor());
                $logger->error($throwable->getMessage(), $throwable->getTrace());

            } catch (Exception $e) {

            }

            Trigger::getInstance()->throwable($throwable);
        }
    }



}