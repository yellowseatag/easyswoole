<?php


namespace App\HttpController;

use App\Extension\AccessToken;
use App\Extension\Cache\Redis;
use App\Extension\ClientInfo;
use App\Extension\Exception\InvalidTokenException;
use App\Extension\Exception\WarningException;
use App\Extension\Response\ClientResult;
use App\Foundation\EncryptMethod;
use EasySwoole\Annotation\Annotation;
use EasySwoole\HttpAnnotation\AnnotationController;
use EasySwoole\HttpAnnotation\AnnotationTag\CircuitBreaker;
use EasySwoole\HttpAnnotation\AnnotationTag\Context;
use EasySwoole\HttpAnnotation\AnnotationTag\Di;
use EasySwoole\HttpAnnotation\AnnotationTag\Method;
use EasySwoole\HttpAnnotation\AnnotationTag\Param;


class Base extends AnnotationController
{
    use EncryptMethod;

    protected $redis;

    protected $clientInfo;

    protected $getData = [];

    protected $postData = [];

    protected $encryptType = 'aesCBC';

    protected $jsonOptions = JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES;

    public function __construct(?Annotation $annotation = null)
    {
        if($annotation == null){
            $annotation = new Annotation();
            /*
                * 注册解析命令
            */
            $annotation->addParserTag(new Method());
            $annotation->addParserTag(new Param());
            $annotation->addParserTag(new Context());
            $annotation->addParserTag(new Di());
            $annotation->addParserTag(new CircuitBreaker());
            $annotation->addParserTag(new \App\Extension\Annotation\AccessToken());
        }
        parent::__construct($annotation);
        $this->redis = (new Redis)->getClient();
    }


    public function onRequest(?string $action): ?bool
    {
        $this->verifyHeader();
        $this->getData = $this->request()->getQueryParams();
        $this->verifyPost($this->getData['md5'] ?? '');
        return parent::onRequest($action);
    }


    protected function isEncrypt()
    {
        return true;
    }


    protected function verifyHeader()
    {
        $clientInfo = $this->request()->getHeader('client-info');
        $sign = $this->request()->getHeader('sign');

        if (empty($clientInfo) || empty($sign)) {
            throw new WarningException('头部参数错误');
        }

        if ($sign[0] != $this->headerMd5Encrypt($clientInfo[0])) {
            throw new WarningException('头部验签失败');
        }

        $clientInfo = json_decode(base64_decode($clientInfo[0]), true);

        if (empty($clientInfo)) {
            throw new WarningException('头部数据为空');
        }

        $clientInfo['ip'] = $this->getUserIp();

        //拼音平台转换
        switch ($clientInfo['platform']){
            case 3:$clientInfo['platform']=2;break;
            case 4:$clientInfo['platform']=1;break;
        }

        $this->clientInfo = new ClientInfo($clientInfo);
     }


     protected function getUserIp()
     {
         if (isset($_SERVER['REMOTE_ADDR'])) {
             return $_SERVER['REMOTE_ADDR'];
         }

         $request = $this->request()->getSwooleRequest();
         return isset($request->header['x-forwarded-for']) ? $request->header['x-forwarded-for'] : $request->server['remote_addr'];
     }


    protected function verifyPost($sign)
    {
        if (empty($this->files()))
        {
            $postData = $this->request()->getBody()->__toString();

            if ($postData) {

                if ($sign != $this->md5Encrypt($postData)) {
                    throw new WarningException('参数验签失败');
                }

                $encryptMethod = $this->encryptType . 'Decrypt';
                $data = $this->$encryptMethod($postData);
                $data = json_decode($data, true);
                if (is_null($data) || !is_array($data)) {
                    throw new WarningException('参数错误');
                }
            }
        }

        $this->postData = array_merge($this->postData, $data ?? []);
    }


    protected function getClientInfo()
    {
        return $this->clientInfo;
    }


    protected function post($key, $default = '', \Closure $valid = null, $errorMesage = '')
    {
        $value = isset($this->postData[$key]) ? $this->postData[$key] : $default;

        if (is_null($valid)) {
            return $value;
        }

        $ret = $valid($value);

        if (!$ret) {
            if (!empty($errorMesage)) {
                throw new WarningException($errorMesage);
            } else {
                throw new WarningException($key . '验证失败');
            }
        }

        if (!is_bool($ret)) {
            return $ret;
        }
        return $value;
    }


    public function index() {}


    protected function output($result)
    {
        if (is_null($result)) {
            return;
        }

        if ($result instanceof ClientResult) {
            $this->outputClientResult($result);
        } else {
            $this->outputRaw($result);
        }
    }


    protected function outputClientResult(ClientResult $result)
    {
        $this->response()->withHeader('Content-type','application/json;charset=utf-8');
        $this->response()->withStatus($result->getStatusCode());

        $res = $result->output();

        if ($this->isEncrypt() and $res['data'] !== '') {
            $encryptMethod = $this->encryptType . 'Encrypt';
            $res['data'] = $this->$encryptMethod($res['data']);
        }

        $this->response()->write(json_encode($res,$this->jsonOptions));
    }


    protected function outputRaw($result)
    {
        if (is_object($result) || is_array($result)) {
            $result = json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $this->response()->withHeader('Content-type', 'application/json;charset=utf-8');
        }
        $this->response()->write($result);
    }


    protected function business($bo = '')
    {
        if (empty($bo)) {
            $bo = get_classname($this) . 'Bo';
        }
        $boClass = "\App\Business\\" . $bo;

        return new $boClass($this->clientInfo, $this->postData);
    }


    protected function __annotationHook(string $actionName)
    {
        $methodAnnotations = $this->getMethodAnnotations();
        if (isset($methodAnnotations[$actionName]) and isset($methodAnnotations[$actionName]['AccessToken'])) {
            if (empty($this->getData['access_token'])) {
                throw new WarningException('access_token为空');
            }
            $accessToken = new AccessToken($this->clientInfo);
            if (!$accessToken->isOnline($this->getData['access_token'])) {
                throw new InvalidTokenException('您的账号已在其他设备登录，请重新登录。');
            }
        }
        return parent::__annotationHook($actionName);
    }
    /**
     * 所有文件
     *
     * @return mixed
     */
    protected function files()
    {
        return $this->request()->getSwooleRequest()->files;
    }

    /**
     * 文件
     *
     * @param null $key
     * @return null
     */
    protected function file($key = null)
    {
        if (empty($key))
        {
            $files = $this->files();

            if (count($files) > 0)
            {
                foreach ($this->files() as $file)
                {
                    return $file;
                }
            }

            return null;
        }
        return null_if_unset($this->files()[$key]);
    }
}