<?php
/**
 * Created by PhpStorm.
 * User: immusen
 * Date: 2019/01/22
 * Time: 2:20 PM
 */

namespace immusen\websocket\src;
/**
 * Class Task Model
 * @package immusen\mqtt\src
 */
class Task
{
    public $fd = 0;
    public $class = 'common';
    public $method = 'default';
    public $param = '';
    public $rpc_id = 1;
    public $route = '';

    const VERB_RPC = 'rpc';
    const VERB_ASYNC = 'async';
    const VERB_INTERNAL = 'internal';

    public function __construct($fd, $route, $param = '', $verb = 'publish', $rpc_id = 1)
    {
        $this->fd = $fd;
        $this->verb = $verb;
        $this->rpc_id = $rpc_id;
        $this->param = $param;
        $this->route = $route;
        $this->resolve($route);
    }

    public static function rpc($fd, $method, $param, $id)
    {
        return new static($fd, $method, $param, 'rpc', $id);
    }

    /**
     * Redis subscribe task
     *
     * Can play like this: $redis->publish('supervisor', 'channel/play/100011'),
     * then the task will do something like mqtt publish
     *
     * @param $message
     * @return static
     */
    public static function async($message)
    {
        return new static(0, $message, '', 'async');
    }

    /**
     * internal job
     * @param $route
     * @param $param
     * @return static
     */
    public static function internal($route, $param = '')
    {
        return new static(0, $route, $param, 'internal');
    }

    private function resolve($route)
    {
        if (preg_match('/(\w+)\/?(\w*)\/?(.*)/s', $route, $routes)) {
            var_dump($routes);
            $this->class = $routes[1];
            $this->method = $routes[2] ?: 'default';
            if ($this->verb == 'async' && !empty($routes[3]))
                $this->param = $routes[3];
        }
    }

//    public function __toString()
//    {
//        return '#Task# verb: ' . $this->verb . ' controller: ' . $this->class . ' action: ' . $this->func . ' param: ' . var_export($this->param, 1) . ' payload: ' . $this->body . PHP_EOL;
//    }

}