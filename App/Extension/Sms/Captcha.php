<?php


namespace App\Extension\Sms;


use App\Extension\Cache\Redis;
use App\Extension\Exception\WarningException;
use App\Extension\Logger;
use EasySwoole\Utility\Random;

class Captcha
{

    private $phone;

    private $app_id;

    private $redis;

    private $prefix = 'course_user:captcha';

    private $testPhone = [
        '18012345678' => '123456',
    ];

    public function __construct($app_id, $phone = '')
    {
        if (empty($phone)) {
            throw new WarningException('手机号码为空');
        }

        if (!is_phone_number($phone)) {
            throw new WarningException('手机号码格式错误');
        }

        $this->phone = $phone;
        $this->app_id = $app_id;
        $this->redis = (new Redis('token'))->getClient();
    }


    /**
     * 获取验证码
     * @return mixed
     */
    public function get()
    {
        return $this->redis->get($this->prefix . ":{$this->app_id}:{$this->phone}");
    }


    /**
     * 验证码验证
     * @param $code
     * @return bool
     */
    public function check($code)
    {
        //测试手机后及验证码判断
        if (isset($this->testPhone[$this->phone]) and $this->testPhone[$this->phone] == $code) {
            return true;
        }

        $captcha = $this->get();
        $param = ['captcha'=>$captcha, 'code'=>$code];
        if ($captcha and $captcha == $code) {
            $this->redis->del($this->prefix . ":{$this->app_id}:{$this->phone}");
            $this->redis->del($this->prefix . ":{$this->app_id}:{$this->phone}:frequent");
            $param['check'] = 1;
            Logger::log($param, $this->phone.'手机验证成功');
            return true;
        }
        $param['check'] = 0;
        Logger::log($param, $this->phone.'手机验证失败');
        return false;
    }


    /**
     * 验证码发送次数判断
     * @return mixed
     */
    public function tooFrequent()
    {
        return $this->redis->exists($this->prefix . ":{$this->app_id}:{$this->phone}:frequent");
    }


    /**
     * 发送验证码
     * @param $app_id
     * @return bool|string
     */
    public function send()
    {
        $captcha = Random::number();
        $this->redis->set($this->prefix . ":{$this->app_id}:{$this->phone}", $captcha, 300);
        $this->redis->set($this->prefix . ":{$this->app_id}:{$this->phone}:frequent", 1, 60);
        Sms253::send($this->phone, "您的短信验证码是：{$captcha} ，限5分钟内使用，如非本人操作，请忽略此短信。", $this->app_id);
        return $captcha;
    }
}