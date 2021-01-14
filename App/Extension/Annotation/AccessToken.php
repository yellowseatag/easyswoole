<?php


namespace App\Extension\Annotation;


use EasySwoole\Annotation\AbstractAnnotationTag;
use EasySwoole\Annotation\ValueParser;

final class AccessToken extends AbstractAnnotationTag
{

    public $action = 'handle';

    public function tagName(): string
    {
        return 'AccessToken';
    }


    public function assetValue(?string $raw)
    {
        $arr = ValueParser::parser($raw);
        if(!empty($arr['handle'])){
            $this->action = $arr['handle'];
        }
    }

}