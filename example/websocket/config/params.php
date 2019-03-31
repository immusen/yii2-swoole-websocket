<?php
return [
    'swoole' => [
        'address' => '0.0.0.0',
        'port' => 8721,
        'server' => [
            'daemonize' => 0,
            'worker_num' => 2,
            'task_worker_num' => 2,
            'debug_mode' => 1,
            'heartbeat_check_interval' => 60,
            'heartbeat_idle_time' => 180,
            'log_file' => Yii::getAlias('@websocket') . '/runtime/logs/app.log',
            'pid_file' => Yii::getAlias('@websocket') . '/runtime/app.pid',
            //More option @see https://wiki.swoole.com/wiki/page/274.html
        ]
    ],
    'redis' => [
        'host' => '127.0.0.1',
        'port' => '6379',
//        'auth' => 'passwd',
        'pool_size' => 10,
    ],
];
