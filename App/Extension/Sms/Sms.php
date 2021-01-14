<?php


namespace App\Extension\Sms;


abstract class Sms
{
    abstract static function send($phone, $content, $app_id);

    public static function prefix()
    {
        return strtoupper(get_classname(new static()));
    }
}