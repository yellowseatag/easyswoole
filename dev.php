<?php
return [
    'SERVER_NAME' => env('SWOOLE_SERVER_NAME', 'EasySwoole'),
    'MAIN_SERVER' => [
        'LISTEN_ADDRESS' => '0.0.0.0',
        'PORT' => env('SWOOLE_PORT', '9551'),
        'SERVER_TYPE' => EASYSWOOLE_WEB_SERVER, //可选为 EASYSWOOLE_SERVER  EASYSWOOLE_WEB_SERVER EASYSWOOLE_WEB_SOCKET_SERVER,EASYSWOOLE_REDIS_SERVER
        'SOCK_TYPE' => SWOOLE_TCP,
        'RUN_MODEL' => SWOOLE_PROCESS,
        'SETTING' => [
            'worker_num' => env('SWOOLE_WORKER_NUM', 8),
            'reload_async' => true,
            'max_wait_time'=> env('SWOOLE_MAX_WAIT_TIME', 3)
        ],
        'TASK'=>[
            'workerNum' => env('SWOOLE_TASK_WORKER_NUM', 4),
            'maxRunningNum' => env('SWOOLE_TASK_MAX_RUNNING_NUM', 128),
            'timeout' => env('SWOOLE_TASK_TIMEOUT', 15)
        ]
    ],
    'TEMP_DIR' => env('SWOOLE_TEMP_DIR', '/tmp'),
    'LOG_DIR' => null,
    'MYSQL' => [
        'default' => [
            'host'          => env('DEFAULT_MYSQL_HOST', 'localhost'),
            'port'          => env('DEFAULT_MYSQL_PORT', '3306'),
            'user'          => env('DEFAULT_MYSQL_USER', ''),
            'password'      => env('DEFAULT_MYSQL_PASSWORD', ''),
            'database'      => env('DEFAULT_MYSQL_DATABASE', ''),
            'timeout'       => env('DEFAULT_MYSQL_TIMEOUT', '5'),
            'charset'       => env('DEFAULT_MYSQL_CHARSET', 'utf8mb4'),
        ],
        'account' => [
            'host'          => env('READ_MYSQL_HOST', 'localhost'),
            'port'          => env('READ_MYSQL_PORT', '3306'),
            'user'          => env('READ_MYSQL_USER', ''),
            'password'      => env('READ_MYSQL_PASSWORD', ''),
            'database'      => env('READ_MYSQL_DATABASE', ''),
            'timeout'       => env('READ_MYSQL_TIMEOUT', '5'),
            'charset'       => env('READ_MYSQL_CHARSET', 'utf8mb4'),
        ],
    ],
    'MONGODB' => [
        'default' => [
            'driver' => 'mongodb',
            'host' => env('DEFAULT_MONGODB_HOST', 'localhost'),
            'port' => env('DEFAULT_MONGODB_PORT', '27017'),
            'database' => env('DEFAULT_MONGODB_DATABASE', 'course_user'),
            'username' => env('DEFAULT_MONGODB_USERNAME', ''),
            'password' => env('DEFAULT_MONGODB_PASSWORD', ''),
        ],
        'account' => [
            'driver' => 'mongodb',
            'host' => env('ACCOUNT_MONGODB_HOST', 'localhost'),
            'port' => env('ACCOUNT_MONGODB_PORT', '27017'),
            'database' => env('ACCOUNT_MONGODB_DATABASE', 'course_user'),
            'username' => env('ACCOUNT_MONGODB_USERNAME', ''),
            'password' => env('ACCOUNT_MONGODB_PASSWORD', ''),
        ],
    ],
    'REDIS' => [
        'default' => [
            'host' => env('COMMON_REDIS_HOST', 'localhost'),
            'port' => env('COMMON_REDIS_PORT', '6379'),
            'auth' => env('COMMON_REDIS_AUTH', ''),
            'db' => is_null(env('COMMON_REDIS_DB', null)) ? null : intval(env('COMMON_REDIS_DB')),
        ],
        'data' => [
            'host' => env('DATA_REDIS_HOST', 'localhost'),
            'port' => env('DATA_REDIS_PORT', '6379'),
            'auth' => env('DATA_REDIS_AUTH', ''),
            'db' => is_null(env('DATA_REDIS_DB', null)) ? null : intval(env('DATA_REDIS_DB')),
        ],
        'token' => [
            'host' => env('TOKEN_REDIS_HOST', 'localhost'),
            'port' => env('TOKEN_REDIS_PORT', '6379'),
            'auth' => env('TOKEN_REDIS_AUTH', ''),
            'db' => is_null(env('TOKEN_REDIS_DB', null)) ? null : intval(env('TOKEN_REDIS_DB')),
        ]
    ],
    'RPC_SERVER' => [
        'serverIp' => env('RPC_SERVER_IP', '127.0.0.1'),
        'listenPort' => env('RPC_SERVER_PORT', '9600'),
    ],
    'RPC_REDIS' => [
        'host' => env('RPC_REDIS_HOST', 'localhost'),
        'port' => env('RPC_REDIS_PORT', '6379'),
        'auth' => env('RPC_REDIS_AUTH', ''),
        'db' => is_null(env('RPC_REDIS_DB', null)) ? null : intval(env('RPC_REDIS_DB')),
    ],
];
