<?php
if (YII_TYPE){
    $params = array_merge(
        require __DIR__ . '/../../common/config/params.php',
        require __DIR__ . '/../../common/config/params-local.php',
        require __DIR__ . '/params.php',
        require __DIR__ . '/params-local.php'
    );
} else {
    $params = array_merge(
        require __DIR__ . '/../../config/params.php',
        require __DIR__ . '/params.php',
        require __DIR__ . '/params-local.php'
    );
}

return [
    'id' => 'websocket',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'websocket\controllers',
    'components' => [
        'errorHandler' => ['class' => 'yii\console\ErrorHandler'],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
    ],
    'params' => $params,
];
