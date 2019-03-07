<?php
/**
 * Created by PhpStorm.
 * User: immusen
 * Date: 2019/1/27
 * Time: 19:56
 */

namespace websocket\controllers;

use immusen\websocket\src\Controller;

class UserController extends Controller
{
    /**
     * Auth and bind user id on connection
     * @param $token
     * @return bool
     * ```json
     *{
     *      "jsonrpc":"2.0",
     *      "id":1,
     *      "method":"user/auth",
     *      "params":{
     *           "token":"xxxx"
     *      }
     * }
     * ```
     */
    public function actionAuth($token)
    {
        //$user = User::getUserByToken($token)
        $user = [
            'id' => 100011,
            'nickname' => 'Foo',
            'avatar' => '1.png'
        ];
        $this->server->bind($this->fd, $user['id']);
        return $this->publish($this->fd, ['user' => $user]);
    }

}