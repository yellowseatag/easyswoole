<?php


namespace App\Extension\Exception;


class WarningException extends \Exception
{
    public function getMsgCode()
    {
        return '0';
    }

    public function getErrorFlag()
    {
        return false;
    }
}