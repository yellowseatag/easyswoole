<?php


namespace App\Extension;


use EasySwoole\Rpc\Response;
use EasySwoole\Rpc\Rpc;

class RpcClient
{

    protected function call(string $service, string $action, $arg = null)
    {
        $rpcClient = Rpc::getInstance()->client();
        $result = [];
        $rpcClient->addCall($service, $action, $arg)
            ->setOnSuccess(function (Response $response) use (&$result) {
                $result = $response->toArray();
            })->setOnFail(function (Response $response) use ($service, $action, $arg) {
                $this->logger([
                    'arg' => $arg,
                    'response' => $response->toArray()
                ],"请求{$service}.{$action}失败!", 300);
            });
        $rpcClient->exec(0.5);
        return $result['result'] ?? null;
    }
}