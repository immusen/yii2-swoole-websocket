<?php
/**
 * Created by PhpStorm.
 * User: immusen
 * Date: 2018/10/14
 * Time: 4:13 PM
 */

namespace websocket\controllers;

use immusen\websocket\src\Controller;

/**
 * Chat room demo
 * Class RoomController
 * @package mqtt\controllers
 */
class RoomController extends Controller
{

    const ROOM_MEMBER_COUNT_KEY = 'ws_record_hash_#room';

    /**
     * Client who join a room
     * @param $id , room id
     * @param null $info , some extra data
     * @return bool
     */
    public function actionJoin($id, $info = null)
    {
        #AUTH
        //$client_info = $this->server->connection_info($this->fd);
        //User auth and client bind @see \websocket\controllers\UserController::actionAuth
        //if (empty($client_info['uid']))
        //    return $this->publish($this->fd, ['type' => 'error', 'msg' => 'Permission denied']);

        //add fd into group with room id as the key
        $this->addFds($this->fd, $id);

        $member_count = $this->redis->hincrby(self::ROOM_MEMBER_COUNT_KEY, $id, 1);

        //Get all fds in this room/group;
        $targets = $this->getFds($id);
        //Broadcast to every client
        return $this->publish($targets, ['type' => 'join', 'count' => $member_count, 'info' => $info]);

        //Or send history message to this client (fake code)
        //$this->publish($this->fd, $this->getHistoryMessageFunction());
    }

    /**
     * some one leave room similar with actionJoin
     * @param $id , room id
     * @param $info
     * @return bool
     */
    public function actionLeave($id, $info = null)
    {
        $member_count = $this->redis->hincrby(self::ROOM_MEMBER_COUNT_KEY, $id, -1);
        $this->publish($this->getFds($id), ['type' => 'leave', 'count' => $member_count, 'info' => $info]);
        //del fd from group
        return $this->delFds($this->fd, $id);
    }

    /**
     * Message to room, e.g. room/msg
     * ```JSON
     * {
     *       "jsonrpc":"2.0",
     *       "id":1,
     *       "method":"room/msg",
     *       "params":{
     *           "id":"100111",
     *           "content":{
     *               "text":"Hello world!"
     *           }
     *       }
     *   }
     * ```
     * @param $id
     * @param $content
     * @return bool
     */
    public function actionMsg($id, $content = null)
    {
        return $this->publish($this->getFds($id), $content);
    }

}