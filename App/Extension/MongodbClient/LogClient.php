<?php


namespace App\Extension\MongodbClient;

use Jenssegers\Mongodb\Connection;

class LogClient
{

    /**
     * mongodb Client
     * @var \MongoDB\Client|null
     */
    private static $instance = null;

    /**
     * 保存用户的自定义配置参数
     * @var array
     */
    private $setting = [];


    /**
     * 构造器私有化:禁止从类外部实例化
     * LogClient constructor.
     */
    private function __construct(){
        $config = [
            'host'=>env('LOG_MONGODB_HOST', 'localhost'),
            'port'=>env('LOG_MONGODB_PORT', '27017'),
            'database'=>env('LOG_MONGODB_HOST', 'localhost'),
            'username'=>env('LOG_MONGODB_USERNAME'),
            'password'=>env('LOG_MONGODB_PASSWORD'),
        ];
        self::$instance = (new Connection($config))->getMongoClient();
    }


    /**
     * 克隆方法私有化:禁止从外部克隆对象
     */
    private function __clone(){}

    /**
     * 因为用静态属性返回类实例,而只能在静态方法使用静态属性
     * 所以必须创建一个静态方法来生成当前类的唯一实例
     * @return \MongoDB\Client|null
     */
    public static function getInstance()
    {
        //检测当前类属性$instance是否已经保存了当前类的实例
        if (self::$instance == null) {
            //如果没有,则创建当前类的实例
            new self();
        }
        //如果已经有了当前类实例,就直接返回,不要重复创建类实例
        return self::$instance;
    }

    /**
     * 设置配置项
     * @param $index
     * @param $value
     */
    public function set($index, $value)
    {
        $this->setting[$index] = $value;
    }

    /**
     * 读取配置项
     * @param $index
     * @return mixed
     */
    public function get($index)
    {
        return $this->setting[$index];
    }

}