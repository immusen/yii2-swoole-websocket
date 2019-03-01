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
     * @param $fds mixed string|array
     * @param $content
     * @return bool
     */
    public function publish($fds, $content)
    {
        if (!is_array($fds)) $fds = array($fds);
        $msg = $this->buildResponse($content);
        var_dump($msg);
        $result = 1;
        while ($fds) {
            $fd = (int)array_pop($fds);
            if ($this->server->isEstablished($fd))
                $result &= $this->server->push($fd, $msg) ? 1 : 0;
        }
        return !!$result;
    }

    /**
     * get fd by key
     * @param $key , group key
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
     * @param string $key , group key
     * @return mixed
     */
    public function addFds($fd, $key = '')
    {
        $key = ($key == '') ? $this->route : $key;
        return $this->redis->sadd(self::GROUP_PREFIX . $key, $fd);
    }

    /**
     * remove key from group
     * @param $fd
     * @param string $key , group key
     * @return mixed
     */
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
    private function buildResponse($content)
    {
        $response = new Response($this->rpc_id);
        $response->setResult($content);
        return $response->serialize();
    }
}
