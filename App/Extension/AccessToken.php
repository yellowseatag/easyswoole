<?php


namespace App\Extension;


use App\Extension\Cache\Redis;
use App\Extension\Exception\InvalidTokenException;
use App\Extension\Exception\WarningException;
use Carbon\Carbon;

class AccessToken
{
    /**
     * accessToken
     * u : userInfo['user_id'],
     * d : clientInfo['device_id']
     * a : clientInfo['app_id']
     *
     * 有序集合
     * 方式一  course_user:login:access_token:app_id:{app_id}:user_id:{user_id}    device_id_{device_id}   timestamp
     * 方式二  course_user:login:access_token:group_id:{group_id}:user_id:{user_id}    device_id_{device_id}   timestamp
     *
     * 踢人逻辑
     * zRemRangeByRank key 0 -{onlineNum}
     *
     * 在线验证逻辑
     * timestamp = zScore key device_id_{device_id}
     * if timestamp + onlineInterval < now : zRem key device_id_{device_id}
     * else zAdd key now device_id_{device_id}
     */

    private $clientInfo;

    private $redis;

    public function __construct(ClientInfo $clientInfo)
    {
        $this->clientInfo = $clientInfo;
        $this->redis = (new Redis('token'))->getClient();
    }


    /**
     * 登记
     * @return string
     */
    public function register()
    {
        $redisKey = $this->getRedisKey($this->clientInfo);
        $this->redis->zAdd($redisKey[0], Carbon::now()->timestamp, $redisKey[1]);
        //账号挤退
        $this->sideline();
        $accessToken = [
            'u' => $this->clientInfo->account_id,
            'd' => $this->clientInfo->device_id,
            'a' => $this->clientInfo->app_id,
        ];
        return Crypto::aesTokenEncrypt(json_encode($accessToken));
    }


    /**
     * 在线验证
     * @param $accessToken
     * @return bool
     * @throws WarningException
     */
    public function isOnline($accessToken)
    {
        /**
         * 在线验证逻辑
         * timestamp = zScore key device_id_{device_id}
         * if timestamp + onlineInterval < now : zRem key device_id_{device_id}
         * else zAdd key now device_id_{device_id}
         */
        $accessToken = Crypto::aesTokenDecrypt($accessToken);

        if (empty($accessToken)) {
            throw new InvalidTokenException('登录参数错误，请重新登录。');
        }

        $accessToken = json_decode($accessToken, true);
        if (empty($accessToken)) {
            throw new InvalidTokenException('登录数据非法，请重新登录。');
        }

        if (
            $accessToken['u'] != $this->clientInfo->account_id ||
            $accessToken['d'] != $this->clientInfo->device_id ||
            $accessToken['a'] != $this->clientInfo->app_id
        ) {
            throw new InvalidTokenException('身份验证失败，请重新登录。');
        }

        $redisKey = $this->getRedisKey($this->clientInfo);
        $timestamp = $this->redis->zScore(...$redisKey);
        if (empty($timestamp)) {
            throw new InvalidTokenException('您的账号已退出，请重新登录。');
        }

        if (Carbon::createFromTimestamp($timestamp)->addDays($this->clientInfo->online_interval)->isPast()) {
            $this->redis->zRem(...$redisKey);
            throw new InvalidTokenException('登录已过期，请重新登录。');
        }

        $this->redis->zAdd($redisKey[0], Carbon::now()->timestamp, $redisKey[1]);
        return true;
    }


    public function getRedisKey(ClientInfo $clientInfo)
    {
        /**有序集合
         * 方式一  course_user:login:access_token:app_id:{app_id}:user_id:{user_id}    device_id_{device_id}   timestamp
         * 方式二  course_user:login:access_token:group_id:{group_id}:user_id:{user_id}    device_id_{device_id}   timestamp
         */
        $key = $clientInfo->group_id ? "course_user:login:access_token:app_id:{$clientInfo->group_id}:user_id:{$clientInfo->account_id}" :
            "course_user:login:access_token:app_id:{$clientInfo->app_id}:user_id:{$clientInfo->account_id}";
        return [
            $key,
            "device_id_{$clientInfo->device_id}"
        ];
    }


    /**
     * 挤退
     * @return bool
     */
    public function sideline()
    {
        /** 踢人逻辑
         * zRemRangeByRank key 0 -{onlineNum}
         */
        $redisKey = $this->getRedisKey($this->clientInfo);
        $this->redis->zRemRangeByRank($redisKey[0], 0, -($this->clientInfo->online_num + 1));
        return true;
    }


    /**
     * 注销
     * @return bool
     */
    public function logout()
    {
        $redisKey = $this->getRedisKey($this->clientInfo);
        $this->redis->zRem(...$redisKey);
        return true;
    }

}