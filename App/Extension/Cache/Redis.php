<?php


namespace App\Extension\Cache;


use EasySwoole\Pool\Manager;

class Redis
{
    private $name = 'default';

    private $redis;

    public function __construct($name = 'default')
    {
        $this->name = $name;
        $this->redis = Manager::getInstance()->get($this->name)->getObj();
    }


    public function getClient()
    {
        return $this->redis;
    }


    public function __destruct()
    {
        Manager::getInstance()->get($this->name)->recycleObj($this->redis);
    }

}