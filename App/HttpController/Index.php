<?php


namespace App\HttpController;


use App\Extension\Exception\WarningException;
use App\Extension\Response\ClientResult;
use EasySwoole\EasySwoole\Config;
use EasySwoole\Http\AbstractInterface\Controller;
use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\Redis\Redis;
use EasySwoole\Rpc\Config as RpcConfig;
use EasySwoole\Rpc\Response;
use EasySwoole\Rpc\Rpc;
use EasySwoole\Utility\File;
use Monolog\Logger;
use OSS\OssClient;
use Oss\Core\OssException;

class Index extends Base
{

    public function index()
    {
        $this->response()->write('hello world');
    }

    protected function actionNotFound(?string $action)
    {
        $this->response()->write('404 not found');
    }

    public function upload()
    {
        $accessKeyID = env('ALIYUN_ACCESS_ID');

        $accessKeySecret = env('ALIYUN_ACCESS_KEY');

        $endpoint = env('ALIYUN_ENDPOINT');

        $bucket = env('ALIYUN_BUCKET');

        try
        {
            $suffix = end(explode('.', $this->file()['name']));

            $filePath = 'pinyin/image/'.get_file_name().'.'.$suffix;

            $ossClient = new OssClient($accessKeyID, $accessKeySecret, $endpoint);

            $ossClient->uploadFile($bucket, $filePath, $this->file()['tmp_name']);

            $this->outputClientResult(new ClientResult(
                ['path' => $filePath]
            ));
        }
        catch (OssException $e)
        {
            throw new WarningException($e->getMessage());
        }
    }
}