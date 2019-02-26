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
    public function actionAuth($token)
    {
        //$user = User::getUserByToken($token)
        $user = [
            'id' => 100011,
            'nickname' => 'Foo',
            'avatar' => '1.png'
        ];
        $this->server->bind($this->fd, $user['id']);
        $this->publish($this->fd, ['user' => $user]);
        return $user;
    }

}