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
 * @package immusen\websocket\src
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
     * then the task will do something like websocket publish
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
     * @param $method
     * @param $param
     * @return static
     */
    public static function internal($method, $param = '')
    {
        return new static(0, $method, $param, 'internal');
    }

    private function resolve($route)
    {
        if (preg_match('/(\w+)\/?(\w*)/s', $route, $routes)) {
            $this->class = $routes[1];
            $this->method = $routes[2] ?: 'default';
        }
    }

}