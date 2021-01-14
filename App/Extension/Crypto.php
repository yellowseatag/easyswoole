<?php


namespace App\Extension;


class Crypto
{

    /**
     * MD5加密
     * @param $data
     * @return string
     */
    public static function md5Encrypt($data):string
    {
        return md5(self::dataToString($data) . self::getEnvSecret(__FUNCTION__));
    }


    /**
     * 头部MD5加密
     * @param $data
     * @return string
     */
    public static function headerMd5Encrypt($data):string
    {
        return md5(self::dataToString($data) . self::getEnvSecret(__FUNCTION__));
    }

    /**
     * 双重MD5加密
     * @param $data
     * @return string
     */
    public static function doubleMd5Encrypt($data):string
    {
        return md5(md5(self::dataToString($data)) . self::getEnvSecret(__FUNCTION__));
    }


    /**
     * 数据转字符串
     * @param $data
     * @return string
     */
    public static function dataToString($data):string
    {
        if (is_array($data) || is_object($data)) {
            $string = json_encode($data);
        } else {
            $string = strval($data);
        }
        return $string;
    }


    public static function getEnvSecret($method, string $default = ''):string
    {
        $key = strtoupper(str_replace(['Encrypt', 'Decrypt'], '', $method)) . '_SECRET';
        return env($key, $default);
    }


    /**
     * xxtea加密
     * @param $data
     * @return string
     */
    public static function xxteaEncrypt($data):string
    {
        return base64_encode(XXTEA::encrypt(self::dataToString($data), self::getEnvSecret(__FUNCTION__)));
    }


    /**
     * xxtea解密
     * @param $data
     * @return string
     */
    public static function xxteaDecrypt($data):string
    {
        return XXTEA::decrypt(base64_decode($data), self::getEnvSecret(__FUNCTION__));
    }


    public static function hmacSHA256Encrypt($data):string
    {
        return base64_encode(hash_hmac('sha256', self::dataToString($data), self::getEnvSecret(__FUNCTION__), true));
    }


    /**
     * aes ecb 加密
     * @param $data
     * @return string
     */
    public static function aesEncrypt($data):string
    {
        return base64_encode(openssl_encrypt(self::dataToString($data), 'AES-128-ECB', self::getEnvSecret(__FUNCTION__)));
    }


    /**
     * aes ecb 解密
     * @param $data
     * @return string
     */
    public static function aesDecrypt($data):string
    {
        return openssl_decrypt(base64_decode($data), 'AES-128-ECB', self::getEnvSecret(__FUNCTION__));
    }


    /**
     * aes cbc 加密
     * @param $data
     * @return string
     */
    public static function aesCBCEncrypt($data):string
    {
        return openssl_encrypt(self::dataToString($data), 'AES-128-CBC', self::getEnvSecret(__FUNCTION__), 0, '8765432187654321');
    }


    /**
     * aes cbc 解密
     * @param $data
     * @return string
     */
    public static function aesCBCDecrypt($data):string
    {
        return openssl_decrypt($data, 'AES-128-CBC', self::getEnvSecret(__FUNCTION__), 0, '8765432187654321');
    }


    /**
     * token 加密
     * @param $data
     * @return string
     */
    public static function aesTokenEncrypt($data):string
    {
        return urlencode(openssl_encrypt(self::dataToString($data), 'AES-128-CBC', self::getEnvSecret(__FUNCTION__), 0, 'sinyee@BabyBus4$'));
    }


    /**
     * token 解密
     * @param $data
     * @return string
     */
    public static function aesTokenDecrypt($data):string
    {
        return openssl_decrypt($data, 'AES-128-CBC', self::getEnvSecret(__FUNCTION__), 0, 'sinyee@BabyBus4$');
    }
    
    
    public static function dataEncrypt($data):string
    {
        return base64_encode(json_encode($data));
    }


    public static function dataDecrypt($data)
    {
        return json_decode(base64_decode($data), true);
    }

}