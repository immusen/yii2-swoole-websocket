<?php
/**
 * Created by PhpStorm.
 * User: immusen
 * Date: 2019/01/21
 * Time: 3:50 PM
 */

namespace immusen\websocket;


use Yii;
use Swoole\Websocket\Server;
use immusen\websocket\src\Task;
use immusen\websocket\src\Redis;
use immusen\websocket\src\rpc\Request;
use immusen\websocket\src\rpc\Response;
use immusen\websocket\src\rpc\Exception;

class Application extends \yii\base\Application
{

    public $server;

    public function run()
    {
        $port = Yii::$app->params['listen'];
        $server = new Server('0.0.0.0', $port, SWOOLE_PROCESS);
        $server->set([
            'worker_num' => 2,
            'task_worker_num' => 2,
            'debug_mode' => 1,
            'heartbeat_check_interval' => 60,
            'heartbeat_idle_time' => 180,
            'daemonize' => Yii::$app->params['daemonize'],
            'log_file' => Yii::$app->getRuntimePath() . '/logs/app.log'
        ]);
        $server->on('Start', [$this, 'onStart']);
        $server->on('Task', [$this, 'onTask']);
        $server->on('Finish', [$this, 'onFinish']);
        $server->on('Connect', [$this, 'onConnect']);
        $server->on('Request', [$this, 'onRequest']);
        $server->on('Message', [$this, 'onMessage']);
        $server->on('Close', [$this, 'onClose']);
        $server->on('WorkerStart', [$this, 'onWorkerStart']);
        //Mount redis on $server
        $server->redis = Redis::getRedis();
        $this->server = $server;
        $this->server->start();
    }

    public function onStart($server)
    {
        echo "Server Start {$server->master_pid}" . PHP_EOL;
    }

    public function onWorkerStart(Server $server, $id)
    {
        if ($id != 0) return;
        go(function () use ($server) {
            $redis = new \Swoole\Coroutine\Redis;
            $config = Yii::$app->params['redis'];
            $result = $redis->connect($config['host'], $config['port']);
            if (!$result) return;
            if (!empty($config['auth']) && !$redis->auth($config['auth'])) return;
            while (true) {
                $result = $redis->subscribe(['rpc']);
                if (empty($result)) continue;
                $this->handleRequest($result[2], -2);
            }
        });
    }

    public function onConnect($server, $fd, $from_id)
    {
        echo '# client connected ' . $fd . ' -=- ' . $from_id . PHP_EOL;
    }

    public function onRequest($request, $response)
    {
        if ($request->server['path_info'] == '/rpc')
            go(function () use ($request, $response) {
                $result = $this->handleRequest($request->get['p'], -1);
                if ($result instanceof Response)
                    return $response->end($result->serialize());
                else
                    return $response->end('{"jsonrpc":2.0,"id":1,"result":"ok"}');
            });
        else
            return $response->end($response->status(404));
    }

    public function onMessage($server, $frame)
    {
        go(function () use ($server, $frame) {
            $result = $this->handleRequest($frame->data, $frame->fd);
            if ($result instanceof Response)
                $server->push($frame->fd, $result->serialize());
        });
    }

    public function onClose($server, $fd, $from)
    {
        $server->task(Task::internal('common/close', ['fd' => $fd]));
    }

    public function onTask(Server $server, $worker_id, $task_id, Task $task)
    {
        try {
            $class = Yii::$app->controllerNamespace . '\\' . ucfirst($task->class) . 'Controller';
            $method = new \ReflectionMethod($class, 'action' . ucfirst($task->method));
            $args = $this->getArgs($method, $task);
            return $method->invokeArgs(new $class($server, $task), $args);
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            var_dump($e->getTraceAsString());
            $response = new Response($task->rpc_id);
            $response->setError([
                'code' => -32603,
                'message' => $e->getMessage(),
            ]);
            $server->push($task->fd, $response->serialize());
        }
    }

    public function onFinish(Server $server, $task_id, $data)
    {
        echo 'Task finished #' . $task_id . PHP_EOL;
        var_dump($data);
    }

    private function getArgs($method, Task $task)
    {
        $params = $task->param;
        $args = [];
        $missing = [];
        if (!is_array($params)) $params = array($params);
        foreach ($method->getParameters() as $param) {
            $name = $param->getName();
            if (array_key_exists($name, $params)) {
                $args[] = $params[$name];
                unset($params[$name]);
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                $missing[] = $name;
            }
        }
        if (!empty($missing)) throw new \Exception('Missing required parameters: ' . implode(',', $missing));
        if (isset($params['__once'])
            && $this->server->redis->del('rpc_con_lock_#' . $task->rpc_id) !== 1) throw new \Exception('Method already running in other instances');
        return $args;
    }

    public function handleRequest($rpc_json, $fd = 0)
    {
        try {
            $request = new Request($rpc_json);
            $id = $request->getId();
            $method = $request->getMethod();
            $params = $request->getParams();
        } catch (Exception $e) {
            if (!isset($id)) $id = 1;
            $response = new Response($id);
            $response->setError($e->getError());
            return $response;
        }
        return $this->server->task(Task::rpc($fd, $method, $params, $id));
    }

}