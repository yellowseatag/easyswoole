<?php
namespace EasySwoole\EasySwoole;


use App\Extension\Exception\HttpExceptionHandler;
use EasySwoole\Component\Di;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\DbManager;
use EasySwoole\Pool\Manager;
use EasySwoole\Redis\Config\RedisConfig;
use EasySwoole\RedisPool\RedisPool;
use EasySwoole\Rpc\NodeManager\RedisManager;
use EasySwoole\Rpc\Config as RpcConfig;
use EasySwoole\Rpc\Rpc;
use EasySwoole\Utility\File;
use Illuminate\Container\Container;
use Illuminate\Events\Dispatcher;

class EasySwooleEvent implements Event
{

    public static function initialize()
    {
        // 注册时区
        date_default_timezone_set('Asia/Shanghai');

        // 注册异常处理
        Di::getInstance()->set(SysConst::HTTP_EXCEPTION_HANDLER,[HttpExceptionHandler::class,'handle']);

        foreach (Config::getInstance()->getConf('REDIS') as $name => $redisConf) {
            //注册缓存连接池
            $redisConfig = new RedisConfig($redisConf);
            Manager::getInstance()->register(new RedisPool($redisConfig), $name);
        }
    }

    public static function mainServerCreate(EventRegister $register)
    {
        // 数据库配置
        $mysqlConfig = Config::getInstance()->getConf('MYSQL');

        //循环注册数据库管理
        foreach ($mysqlConfig as $connectionName => $dbConf) {
            $config = new \EasySwoole\ORM\Db\Config($dbConf);
            DbManager::getInstance()->addConnection(new Connection($config), $connectionName);
        }

        //定义节点Redis管理器
        $redisPool = new RedisPool(new RedisConfig(Config::getInstance()->getConf('RPC_REDIS')));
        $manager = new RedisManager($redisPool);

        //配置Rpc实例
        $config = new RpcConfig(Config::getInstance()->getConf('RPC_SERVER'));
        $config->setNodeManager($manager);

        //配置初始化
        Rpc::getInstance($config);
        //添加服务
        foreach (File::scanDirectory('App/RpcService')['files'] as $file) {
            $serviceName = str_replace('/', '\\', substr($file, 0, -4));
            if ($serviceName == 'App\\RpcService\\BaseService') {
                continue;
            }
            Rpc::getInstance()->add(new $serviceName());
        }
        Rpc::getInstance()->attachToServer(ServerManager::getInstance()->getSwooleServer());

        //mongodb连接池管理
        $capsule = new \Illuminate\Database\Capsule\Manager();
        foreach (Config::getInstance()->getConf('MONGODB') as $name => $mongoConf) {
            $capsule->addConnection($mongoConf, $name . '-mongodb');
        }
        $capsule->setEventDispatcher(new Dispatcher(new Container()));
        $capsule->getDatabaseManager()->extend('mongodb', function ($config, $name) {
            $config['name'] = $name;
            return new \Jenssegers\Mongodb\Connection($config);
        });
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }

    public static function onRequest(Request $request, Response $response): bool
    {
        // TODO: Implement onRequest() method.
        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        // TODO: Implement afterAction() method.
    }
}