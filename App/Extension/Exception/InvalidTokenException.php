<?php


namespace App\Extension\Exception;


class InvalidTokenException extends \Exception
{

    public function getMsgCode()
    {
        return '001001';
    }

    public function getErrorFlag()
    {
        return false;
    }

}