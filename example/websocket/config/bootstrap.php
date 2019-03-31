<?php
//This set will insure (new immusen\websocket\Application($config);) running in portal shell ./websocket-server
Yii::setAlias('@immusen/websocket', dirname(dirname(__DIR__)) . '/vendor/immusen/yii2-swoole-websocket');
Yii::setAlias('@websocket', dirname(dirname(__DIR__)) . '/websocket');