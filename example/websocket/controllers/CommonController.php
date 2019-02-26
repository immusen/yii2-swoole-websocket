<?php
/**
 * Created by PhpStorm.
 * User: immusen
 * Date: 2018/10/14
 * Time: 11:07 PM
 */

namespace websocket\controllers;

use immusen\websocket\src\Controller;

class CommonController extends Controller
{
    /**
     * Client close
     * @param $fd
     */
    public function actionClose($fd)
    {
        echo '#client closed: ', $fd, PHP_EOL;
    }

    /**
     * Control redis by websocket rpc or trigger by other caller as a async task
     * @param $verb
     * @param $param
     * @return mixed
     */
    public function actionRedis($verb, $param)
    {
//        if ($this->verb !== Task::VERB_INTERNAL) return false;  // only accept internal call
        return call_user_func_array([$this->redis, $verb], $param);
    }

    public function actionDefault($_)
    {
        //
    }
}