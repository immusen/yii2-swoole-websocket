Websocket rpc server For Yii2 Base On Swoole 4
==============================
Websocket server for Yii2 base on swoole 4, Support JSONRPC, Resolve 'method' as a route reflect into controller/action, And support http or redis pub/sub to trigger async task from your web application.

Installation
------------
Install Yii2: [Yii2](https://www.yiiframework.com).

Install swoole: [swoole](https://www.swoole.com), recommend version 4+.

Other dependency: php-redis extension.

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist immusen/yii2-swoole-websocket "~1.0"
```

or add

```
"immusen/yii2-swoole-websocket": "~1.0"
```

to the require section of your `composer.json` file.

or unzip code into vendor directory and then add 

```  
'immusen/yii2-swoole-websocket' => 
     array (
       'name' => 'immusen/yii2-swoole-websocket',
       'version' => 'dev',
       'alias' => 
       array (
         '@immusen/websocket' => $vendorDir . '/immusen/yii2-swoole-websocket',
       ),
     ),

```
to vendor/yiisoft/extensions.php


Test or Usage
-------------


> after installation, cd project root path, e.g. cd yii2-advanced-project/
```
mv vendor/immusen/yii2-swoole-websocket/example/websocket ./
mv vendor/immusen/yii2-swoole-websocket/example/websocket-server ./
chmod a+x ./websocket-server
```
> run:
```
./websocket-server
```
> config :
```
vim ./websocket/config/params.php
<?php
return [
    'listen' => 8721,
    'daemonize' => 0,
];
```
> or coding in ./websocket/controllers/

Example:
--------
Chat room demo, code: ./websocket/controllers/RoomController.php

> client join room: 
websocket client send: 

  ```
    {
        "jsonrpc":"2.0",
        "id":1,
        "method":"room/join",
        "params":{
            "id":"100111",
            "info":{
                "age":"19",
                "gender":"f"
            }
        }
    }
  ```
  > the websocket client which had joined same room (id:100111) will get message like this:
  ```
    {
        "jsonrpc":"2.0",
        "id":1,
        "result":{
            "type":"join",
            "count":85,
            "info":{
                "age":"19",
                "gender":"f"
            }
        }
    }
  ```

> chat message
websocket client send:
```
    {
        "jsonrpc":"2.0",
        "id":1,
        "method":"room/msg",
        "params":{
            "id":"100111",
            "content":{
                "text":"Hello world!"
            }
        }
    }
```
> this room member will get:
```
    {
        "jsonrpc":"2.0",
        "id":1,
        "result":{
            "text":"Hello world!"
        }
    }
```

Coding:
--------
1, Create Controller under websocket/controllers, or other path which defined with "controllerNamespace" in websocket/config/main.php
```
<?php
namespace websocket\controllers;

use immusen\websocket\src\Controller;

class FooController extends Controller
{
     public function actionBar($param_1, $param_2 = 0, param_n = null)
     {
          # add current fd into a group/set, make $param_1 or anyother string as the group/set key
          $this->addFds($this->fd, $param_1);
          
          # reply message to current client by websocket
          $this->publish($this->fd, ['p1' => param_1, 'p2' => param_2]);
          
          # get all fds stored in the group/set
          $fds_array = $this->getFds($param_1);
          
          # reply message to a group
          $this->publish($fds_array, ['p1' => param_1, 'p2' => param_2]);
          
          # operate redis via redis pool
          $this->redis->set($param_1, 0)
     }
     
     public function actionBaz()
     {
          //...
     }
}
```

2, Send RPC JSON to trigger that action 
```
    {
        "jsonrpc":"2.0",
        "id":1,
        "method":"foo/bar",
        "params":{
            "param_1":"client_01",
            "param_2":100,
            "param_n":{
                "time":1551408888,
                "type":"report"
            }
        }
    }
```

---

All of client to server rpc command also can send by HTTP or Redis publish, This feature will helpful for some async task triggered from web application. Example in chat room case: 

HTTP request: 
```
http://127.0.0.1:8721/rpc?p={"jsonrpc":"2.0","id":1,"method":"room/msg","params":{"id":"100111","content":{"text":"System warning!"}}}
```
OR redis-cli: 
```
127.0.0.1:6379> publish rpc '{"jsonrpc":"2.0","id":1,"method":"room/msg","params":{"id":"100111","content":{"text":"System warning!"}}}'
```
OR in Yii web application
```
Yii:$app->redis->publish('rpc', '{"jsonrpc":"2.0","id":1,"method":"room/msg","params":{"id":"100111","content":{"text":"System warning!"}}}')
```
