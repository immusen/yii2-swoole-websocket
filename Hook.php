<?php
/**
 * Created by PhpStorm.
 * User: jonny
 * Date: 2019/2/26
 * Time: 14:53
 */

namespace immusen\websocket;

use yii\base\Component;

/**
 * Class Hook as a portal use to call swoole async task worker from Yii2 web application
 * @package immusen\websocket
 */
class Hook extends Component
{

    public function call($method, $param)
    {
        // TODO: hook
    }

}