<?php


namespace App\Extension\RpcClient;

use App\Extension\Logger;
use EasySwoole\Rpc\Response;
use EasySwoole\Rpc\Rpc;


class Client
{
    public static function rpcCall(string $service, string $action, $arg = null)
    {
        $rpcClient = Rpc::getInstance()->client();
        $result = [];
        $rpcClient->addCall($service, $action, $arg)
            ->setOnSuccess(function (Response $response) use (&$result) {
                $result = $response->toArray();
            })->setOnFail(function (Response $response) use ($service, $action, $arg) {
                Logger::log([
                    'arg' => $arg,
                    'response' => $response->toArray()
                ],"请求{$service}.{$action}失败!", 300);
            });
        $rpcClient->exec(0.5);
        return $result['result'] ?? null;
    }
}