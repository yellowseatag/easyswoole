<?php

namespace App\Foundation;

use XXTEA;

trait EncryptMethod
{
    /**
     * MD5加密
     * @param $data
     * @return string
     */
    protected function md5Encrypt($data):string
    {
        return md5($this->dataToString($data) . $this->getEnvSecret(__FUNCTION__));
    }


    /**
     * 头部MD5加密
     * @param $data
     * @return string
     */
    protected function headerMd5Encrypt($data):string
    {
        return md5($this->dataToString($data) . $this->getEnvSecret(__FUNCTION__));
    }

    /**
     * 双重MD5加密
     * @param $data
     * @return string
     */
    protected function doubleMd5Encrypt($data):string
    {
        return md5(md5($this->dataToString($data)) . $this->getEnvSecret(__FUNCTION__));
    }


    /**
     * 数据转字符串
     * @param $data
     * @return string
     */
    protected function dataToString($data):string
    {
        if (is_array($data) || is_object($data)) {
            $string = json_encode($data);
        } else {
            $string = strval($data);
        }
        return $string;
    }


    protected function getEnvSecret($method, string $default = ''):string
    {
        $key = strtoupper(str_replace(['Encrypt', 'Decrypt'], '', $method)) . '_SECRET';
        return env($key, $default);
    }


    /**
     * xxtea加密
     * @param $data
     * @return string
     */
    protected function xxteaEncrypt($data):string
    {
        return base64_encode(XXTEA::encrypt($this->dataToString($data), $this->getEnvSecret(__FUNCTION__)));
    }


    /**
     * xxtea解密
     * @param $data
     * @return string
     */
    protected function xxteaDecrypt($data):string
    {
        return XXTEA::decrypt(base64_decode($data), $this->getEnvSecret(__FUNCTION__));
    }


    protected function hmacSHA256Encrypt($data):string
    {
        return base64_encode(hash_hmac('sha256', $this->dataToString($data), $this->getEnvSecret(__FUNCTION__), true));
    }


    /**
     * aes ecb 加密
     * @param $data
     * @return string
     */
    protected function aesEncrypt($data):string
    {
        return base64_encode(openssl_encrypt($this->dataToString($data), 'AES-128-ECB', $this->getEnvSecret(__FUNCTION__)));
    }


    /**
     * aes ecb 解密
     * @param $data
     * @return string
     */
    protected function aesDecrypt($data):string
    {
        return openssl_decrypt(base64_decode($data), 'AES-128-ECB', $this->getEnvSecret(__FUNCTION__));
    }


    /**
     * aes cbc 加密
     * @param $data
     * @return string
     */
    protected function aesCBCEncrypt($data):string
    {
        return openssl_encrypt($this->dataToString($data), 'AES-128-CBC', $this->getEnvSecret(__FUNCTION__), 0, '8765432187654321');
    }


    /**
     * aes cbc 解密
     * @param $data
     * @return string
     */
    protected function aesCBCDecrypt($data):string
    {
        return openssl_decrypt($data, 'AES-128-CBC', $this->getEnvSecret(__FUNCTION__), 0, '8765432187654321');
    }


    /**
     * aes cbc 加密
     * @param $data
     * @return string
     */
    protected function aesTokenEncrypt($data):string
    {
        return openssl_encrypt($this->dataToString($data), 'AES-128-CBC', $this->getEnvSecret(__FUNCTION__), 0, 'sinyee@BabyBus4$');
    }


    /**
     * aes cbc 解密
     * @param $data
     * @return string
     */
    protected function aesTokenDecrypt($data):string
    {
        return openssl_decrypt($data, 'AES-128-CBC', $this->getEnvSecret(__FUNCTION__), 0, 'sinyee@BabyBus4$');
    }

}