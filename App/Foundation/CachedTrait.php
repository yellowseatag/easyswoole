<?php

namespace App\Foundation;


use App\Extension\Cache\Redis;
use App\Extension\Crypto;
use App\Extension\Logger;
use Carbon\Carbon;

trait CachedTrait
{

    protected $cacheClient;

    protected $redis;

    public function getCacheClient()
    {
        if (empty($this->cacheClient)) {
            $this->cacheClient = (new Redis('data'))->getClient();
        }
        return $this->cacheClient;
    }


    /**
     * ID
     *
     * @param $query
     * @param $id
     * @return mixed
     */
    public function scopeId($query, $id)
    {
        return $query->where($this->schemaInfo()->getPkFiledName(), $id);
    }

    /**
     * ID查询缓存
     *
     * @param $id
     * @param null $expire
     * @return mixed
     */
    public function findCache($id, $expire = null)
    {
        return $this->cached('id', [$id], $expire)->first();
    }

    /**
     * 删除ID缓存
     *
     * @param null $id
     */
    public function deleteFindCache($id = null)
    {
        $this->deleteCachedKey('id', [$id]);
    }


    /**
     * 获得缓存Key
     *
     * @param $scope
     * @param null $time
     * @param array $args
     * @return string
     */
    protected function getCachedKey($scope, $args = [])
    {
        $key = 'cached:' . $this->getTableName() . ':' . $scope;
        if (count($args) > 0) {
            $key .= ':' . join(':', $args);
        }
        return $key;
    }


    /**
     * 删除缓存
     *
     * @param $scope
     * @param array $args
     */
    public function deleteCachedKey($scope, array $args = [])
    {
        $this->getCacheClient()->del($this->getCachedKey($scope, $args));
    }


    /**
     * 获得缓存
     *
     * @param $scope
     * @param array $args
     * @param null $expire
     * @return array|Object|string
     */
    public function cached($scope, array $args = [], $expire = null)
    {
        //设置缓存有效期
        if (empty($expire)) {
            $expire = env('CACHED_TTL', 600);
        }

        //获取缓存
        $redisKey = $this->getCachedKey($scope, $args);
        $cacheData = $this->getObject($redisKey);
        if ($cacheData) {
            return collect($cacheData);
        }

        //没有缓存获取数据
        $scopeMethod = "scope" . ucfirst($scope);
        $data = $this->getAll(function ($query) use ($scopeMethod, $args) {
            $this->$scopeMethod($query, ...$args);
        });

        //数据非空保存缓存
        if (!is_null($data)) {
            $this->setObject($redisKey, $data, $expire);
            $data = collect($data);
        }
        return $data;
    }

    /**
     * @param $key
     * @param $value
     * @param int $expire_ttl
     * @return mixed
     */
    public function setObject($key, $value, $expire_ttl = -1)
    {
        return $this->getCacheClient()->set($key, Crypto::dataEncrypt($value), $expire_ttl);
    }


    /**
     * @param $key
     * @param null $default
     * @return int|mixed|null
     */
    public function getObject($key, $default = null)
    {
        $value = $this->getCacheClient()->get($key, $default);
        if ($value)
        {
            return Crypto::dataDecrypt($value);
        }
        return null;
    }

}