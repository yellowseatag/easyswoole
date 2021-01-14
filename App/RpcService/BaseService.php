<?php


namespace App\RpcService;


use EasySwoole\Rpc\AbstractService;

class BaseService extends AbstractService
{
    public function serviceName(): string
    {
        return get_classname($this);
    }

    protected function args($key, $default = null)
    {
        $args = $this->request()->getArg();

        if (isset($args[$key]))
        {
            return $args[$key];
        }

        return $default;
    }
}