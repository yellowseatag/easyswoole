<?php


namespace App\Extension;


use EasySwoole\Spl\SplBean;

class ClientInfo extends SplBean
{
    //经分设备ID
    public $device_id;

    //设备型号
    public $device_model;

    //设备类型
    public $device_type;

    //平台 1-ios 2-android 3-google 4-海外ios 5-微信公众号 6-微信小程序
    public $platform;

    //操作系统 android ios
    public $os;

    //操作系统版本
    public $os_ver;

    //语言
    public $language;

    //设备分辨率
    public $screen;

    //产品包名
    public $app_key;

    //产品ID
    public $app_id;

    //家长线提供的应用ID
    public $app_product_id;

    //渠道
    public $channel;

    //版本号 eg.9280000
    public $ver;

    //上网类型
    public $net;

    //用户唯一标识
    public $account_id = 0;

    //用户第三方开放平台唯一标识
    public $openid;

    //国家
    public $country;

    //产品年龄
    public $app_age;

    //自媒体广告年龄
    public $ad_age;

    //设备新老用户标识
    public $dev_user_type;

    //应用新老用户标识
    public $app_user_type;

    //登录日志ID
    public $log_id = 0;

    //在线天数
    public $online_interval = 30;

    //同时在线人数
    public $online_num = 1;

    //分组ID
    public $group_id = 0;

    //客户端IP
    public $ip = '127.0.0.1';

    //AB版本测试
    public $flutter_abtest;
}