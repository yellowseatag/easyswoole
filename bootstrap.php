<?php

use Dotenv\Dotenv;

$dotEnv = Dotenv::create([__DIR__]);
$dotEnv->load();

if (! function_exists('env')) {
    /**
     * Gets the value of an environment variable.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        return \Illuminate\Support\Env::get($key, $default);
    }
}

if (! function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if (!function_exists('str_camel2uline'))
{
    /**
     * 驼峰转下划线
     *
     * @param $camelCaps
     * @param string $separator
     * @return string
     */
    function str_camel2uline($camelCaps, $separator='_')
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
    }
}

if (!function_exists('get_class_ds'))
{
    /**
     * 获得类信息
     *
     * @param $object
     * @param string $ds
     *
     * @return string
     */
    function get_class_ds($object, $ds = '/')
    {
        $ret = get_class($object);
        return empty($ds) ? $ret : str_replace('\\', $ds, $ret);
    }

}

if (!function_exists('get_classname'))
{
    /**
     * 获得类名
     *
     * @param $object
     *
     * @return string
     */
    function get_classname($object)
    {
        return basename(get_class_ds($object));
    }

}


if (!function_exists('get_namespace'))
{
    /**
     * 获得命名空间
     *
     * @param $object
     *
     * @return string
     */
    function get_namespace($object)
    {
        return str_replace('/', '\\', dirname(get_class_ds($object)));
    }
}


if (! function_exists('collect')) {
    /**
     * @param null $value
     * @return \Tightenco\Collect\Support\Collection
     */
    function collect($value = null)
    {
        return new \Tightenco\Collect\Support\Collection($value);
    }
}

/**
 * 中国电信号码格式验证
 * 手机段： 133,1349,141,149,153,1700,1701,1702,1740,173,177,180,181,189,191,199
 */
define('CHINA_TELECOM_PATTERN', "(^1(33|4[19]|53|7[37]|8[019]|9[19])\\d{8}$)|(^1(349|70[012]|740)\\d{7}$)");

/**
 * 中国联通号码格式验证
 * 手机段：130,131,132,140,145,146,155,156,166,167,1704,1707,1708,1709,171,175,176,185,186
 */
define('CHINA_UNICOM_PATTERN', "(^1(3[0-2]|4[056]|5[56]|6[67]|7[156]|8[56])\\d{8}$)|(^170[4789]\\d{7}$)");


/**
 * 中国移动号码格式验证
 * 手机段：134,135,136,137,138,139,144,147,148,150,151,152,157,158,159,165,1703,1705,1706,178,182,183,184,187,188,198
 **/
define('CHINA_MOBILE_PATTERN', "(^1(3[4-9]|4[478]|5[0-27-9]|65|78|8[2-478]|98)\\d{8}$)|(^170[356]\\d{7}$)");


if (!function_exists('is_phone_number'))
{
    /**
     * 验证手机号
     *
     * @param $number (0:所有手机, 1:移动, 2: 联通, 3:电信)
     * @param int $type
     * @return bool
     */
    function is_phone_number($number, $type = 0)
    {
        $pattern = '';
        switch ($type)
        {
            case 1:
                $pattern = CHINA_MOBILE_PATTERN;
                break;
            case 2:
                $pattern = CHINA_UNICOM_PATTERN;
                break;
            case 3:
                $pattern = CHINA_TELECOM_PATTERN;
                break;
            default:
                $pattern = join('|', [CHINA_MOBILE_PATTERN, CHINA_UNICOM_PATTERN, CHINA_TELECOM_PATTERN]);
                break;
        }

        return preg_match('/' . $pattern . '/', $number) == 1 ? true : false;
    }
}

if (!function_exists('md5_int64'))
{
    /**
     * 生成int64 ID
     *
     * @param $str
     * @return string
     */
    function md5_int64($str)
    {
        return base_convert_64(substr(md5($str),8,16), 16, 10 );
    }
}

if (!function_exists('base_convert_64'))
{
    function base_convert_64($number, $frombase, $tobase)
    {
        if ($frombase == $tobase) {
            return $number;
        }
        $number = trim($number);
        if ($frombase != 10) {
            $len = strlen($number);
            $fromDec = 0;
            for ($i = 0; $i < $len; $i++) {
                $v = \base_convert($number[$i], $frombase, 10);
                $fromDec = bcadd(bcmul($fromDec, $frombase, 0), $v, 0);
            }
        } else {
            $fromDec = $number;
        }
        if ($tobase != 10) {
            $result = '';
            while (bccomp($fromDec, '0', 0) > 0) {
                $v = intval(bcmod($fromDec, $tobase));
                $result = \base_convert($v, 10, $tobase) . $result;
                $fromDec = bcdiv($fromDec, $tobase, 0);
            }
        } else {
            $result = $fromDec;
        }
        return (string)$result;
    }
}


if (!function_exists('hex2dec'))
{
    /**
     * 十六转十进制
     *
     * @param $hex
     * @return string
     */
    function hex2dec($hex)
    {
        return base_convert_64($hex, 16, 10);
    }
}

if (!function_exists('get_file_name')) {
    /**
     * @desc 获取文件存储路径
     * @return mixed
     * @author liuch
     * @version 1.0
     */
    function get_file_name()
    {

        $time = sprintf('%.4f', microtime(true));

        $random = sprintf('%04d', mt_rand(0, 9999));

        return str_replace('.', $random, $time);

    }
}

if (!function_exists('cdn'))
{
    /**
     * cdn地址
     *
     * @param $file
     * @return string
     */
    function cdn($file)
    {
        if (empty($file) || strpos($file,'http') !== false)
        {
            return $file;
        }

        $cdn = env('CDN', '');

        return $cdn . $file;
    }
}