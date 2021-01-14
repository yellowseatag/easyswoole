<?php
/**
 * Created by PhpStorm.
 * User: breaux liucaihuang@babybus.com
 * Date: 2020/2/12
 * Time: 16:49
 */

namespace App\Extension\Response;


class ClientResult
{

    protected $statusCode = 200;

    protected $status;

    protected $message;

    protected $data;

    public function __construct($data = null, $message = 'success', $status = '1')
    {
        $this->status = strval($status);
        $this->message = $message;
        $this->data = $data;
    }


    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function setStatusCode(int $statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = strval($status);
        return $this;
    }

    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }


    public function output()
    {
        return [
            'status' => $this->status,
            'message' => $this->message,
            'data' => is_null($this->data) ? '' : $this->data
        ];
    }

}