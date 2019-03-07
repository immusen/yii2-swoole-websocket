<?php
/**
 * Created by PhpStorm.
 * User: immusen
 * Date: 2019/01/22
 * Time: 11:27 AM
 */

namespace immusen\websocket\src;

use Swoole\Server;
use immusen\websocket\src\Task;
use immusen\websocket\src\rpc\Response;

class Controller
{
    public $server;
    public $fd;
    public $redis;
    public $task;
    //key prefix of redis set
    const GROUP_PREFIX = 'ws_group_fds_set_#';

    /**
     * BaseController constructor.
     * @param Server $server
     * @param $fd
     * @param $topic
     * @param string $verb
     */
    public function __construct(Server $server, Task $task)
    {
        $this->redis = $server->redis;
        $this->fd = $task->fd;
        $this->task = $task;
        $this->server = $server;
        $this->beforeAction();
    }

    public function beforeAction()
    {

    }

    /**
     * Broadcast publish
     * @param $fds mixed string|array
     * @param $content message
     * @key group key, use to remove fd from group when client not establish
     * @return bool
     */
    public function publish($fds, $content, $key = '')
    {
        if (!is_array($fds)) $fds = array($fds);
        $msg = $this->buildResponse($content);
        $result = 1;
        while ($fds) {
            $fd = (int)array_pop($fds);
            if ($this->server->isEstablished($fd))
                $result &= $this->server->push($fd, $msg) ? 1 : 0;
            else
                if ($key != '') $this->leaveGroup($fd, $key);
        }
        return !!$result;
    }

    /**
     * send message to group
     * @param $content , message
     * @param string $key , group key/name
     */
    public function sendToGroup($content, $key = '')
    {
        $key = ($key == '') ? $this->route : $key;
        $fds = $this->redis->smembers(self::GROUP_PREFIX . $key);
        return $this->publish($fds, $content, $key);
    }

    /**
     * get fd by key
     * @param $key , group key
     * @return array
     */
    public function groupMembers($key = '')
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
    public function joinGroup($fd, $key = '')
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
    public function leaveGroup($fd, $key = '')
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
        $response = new Response($this->task->rpc_id);
        $response->setResult($content);
        return $response->serialize();
    }
}