<?php


namespace App\Extension\Sms;


use App\Extension\Logger;
use EasySwoole\HttpClient\HttpClient;

class Sms253 extends Sms
{
    public static function send($phone, $content, $app_id)
    {
        $prefix = self::prefix();
        $client = new HttpClient(env( $prefix . '_URL', ''));
        $account = env($prefix . '_ACCOUNT_' . $app_id, env($prefix . '_ACCOUNT', ''));
        $password = env($prefix . '_PASSWORD_' . $app_id, env($prefix . '_PASSWORD', ''));
        $respose = $client->postJson(json_encode([
            'account'  =>  $account,
            'password' => $password,
            'msg' => urlencode($content),
            'phone' => $phone,
            'report' => 'true'
        ]));
        Logger::log(['account'=>$account, 'pass'=>$password, 'response'=>$respose->toArray(),
                'account_env'=>$prefix . '_ACCOUNT_' . $app_id, 'pass_env'=>$prefix . '_PASSWORD_' . $app_id]
            , $app_id . '_短信回执日志：' . $phone);
    }
}