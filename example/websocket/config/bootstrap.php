<?php
//This set will insure (new immusen\mqtt\Application($config);) running in portal shell ./mqtt-server
Yii::setAlias('@immusen/websocket', dirname(dirname(__DIR__)) . '/vendor/immusen/yii2-swoole-websocket');
Yii::setAlias('@websocket', dirname(dirname(__DIR__)) . '/websocket');