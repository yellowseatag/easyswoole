<?php


namespace App\Business;


use App\Extension\ClientInfo;
use App\Extension\Logger;

class BaseBo
{
    protected $clientInfo;

    protected $postData;

    public function __construct(ClientInfo $clientInfo, $postData = [])
    {
        $this->clientInfo = $clientInfo;
        $this->postData = $postData;
    }

    /**
     * 客户端请求日志
     * @param $requestData
     * @param $msg
     */
    public function clientRequestLog($requestData, $msg='请求日志'){
        Logger::log(
            ['arg' => $requestData, 'client' => $this->clientInfo]
            ,$this->clientInfo->account_id . '_' . $msg
            , 200
        );
    }

    /**
     * 成功
     * @param $data
     * @param string $message
     * @return array
     */
    public function returnSuccess($data, $message='')
    {
        return  [
            'status'=>1,
            'info'=>$message,
            'data'=>$data
        ];
    }

    /**
     * 失败
     * @param $message
     * @return array
     */
    public function returnFail($message)
    {
        return ['status'=>0, 'info'=>$message, 'data'=>[]];
    }

}