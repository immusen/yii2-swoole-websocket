<?php
/**
 * Created by PhpStorm.
 * User: immusen
 * Date: 2019/01/22
 * Time: 11:27 AM
 */

namespace immusen\websocket\src;

use immusen\websocket\src\rpc\Response;
use Swoole\Server;

class Controller
{
    public $server;
    public $fd;
    public $redis;
    public $route;
    public $rpc_id;
    //key prefix of redis set
    const GROUP_PREFIX = 'ws_group_fds_set_#';

    /**
     * BaseController constructor.
     * @param Server $server
     * @param $fd
     * @param $topic
     * @param string $verb
     */
    public function __construct(Server $server, $fd, $route, $rpc_id = 1)
    {
        $this->fd = $fd;
        $this->redis = $server->redis;
        $this->server = $server;
        $this->route = $route;
        $this->rpc_id = $rpc_id;
    }

    /**
     * Broadcast publish
     * @param $fds
     * @param $content
     * @param int $rpc_id
     * @param string $fds_key, if fds saved by customize key, assign this param. @see \immusen\websocket\src\Controller::addFds
     * @return bool
     */
    public function publish($fds, $content, $rpc_id = 1, $fds_key = '')
    {
        if (!is_array($fds)) $fds = array($fds);
        $msg = $this->buildResponse($content, $rpc_id);
        var_dump($msg);
        $result = 1;
        while ($fds) {
            $fd = (int)array_pop($fds);
            if ($this->server->isEstablished($fd)) {
                $result &= $this->server->push($fd, $msg) ? 1 : 0;
            } else {
                $fds_key = ($fds_key == '') ? $this->route : $fds_key;
                $this->redis->srem(self::GROUP_PREFIX . $fds_key, $fd);
            }
        }
        return !!$result;
    }

    /**
     * get fd by group
     * @param $key
     * @return array
     */
    public function getFds($key = '')
    {
        $key = ($key == '') ? $this->route : $key;
        $res = $this->redis->smembers(self::GROUP_PREFIX . $key);
        if (!$res) return [];
        return $res;
    }

    /**
     * add fd into group
     * @param $fd
     * @param string $key
     * @return mixed
     */
    public function addFds($fd, $key = '')
    {
        $key = ($key == '') ? $this->route : $key;
        return $this->redis->sadd(self::GROUP_PREFIX . $key, $fd);
    }

    public function delFds($fd, $key = '')
    {
        $key = ($key == '') ? $this->route : $key;
        return $this->redis->srem(self::GROUP_PREFIX . $key, $fd);
    }

    /**
     * build rpc response
     * @param $content
     * @param $rpc_id
     * @return string
     */
    private function buildResponse($content, $rpc_id)
    {
        $response = new Response($rpc_id);
        $response->setResult($content);
        return $response->serialize();
    }
}