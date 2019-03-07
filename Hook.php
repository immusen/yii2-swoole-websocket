<?php
/**
 * Created by PhpStorm.
 * User: jonny
 * Date: 2019/2/26
 * Time: 14:53
 */

namespace immusen\websocket;

use Yii;
use yii\base\Component;
use yii\di\Container;


/**
 * Class Hook as a portal use to call swoole async task worker from Yii2 web application, Just like runAction() in Yii;
 * config, e.g. frontend/config/main.php
 * ```php
 * [
 *            'components' => [
 *                // ...
 *                'hook' =>
 *                    [
 *                        'class' => '\immusen\websocket\Hook',
 *                         //optional
 *                        'gateway' => [
 *                            'class' => 'yii\redis\Connection',
 *                            'hostname' => '127.0.0.1',
 *                            //'password' => '123456',
 *                            'port' => 6379,
 *                            //'database' => 0,
 *                        ],
 *                    ],
 *            ],
 *        ]
 * ```
 * usage
 * ```php
 * Yii::$app->hook->run('room/msg', ['id' => 100111, 'content' => 'System warning!']);
 * Yii::$app->hook->runOnce('sms/send', ['mobile' => 15600008721, 'code' => '8721']);
 * ```
 * @package immusen\websocket
 */
class Hook extends Component
{

    public $gateway;
    private $redis;

    /**
     * config special gateway redis if assigned with 'gateway', or it will use default Yii::$app->redis
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function init()
    {
        parent::init();
        if (!empty($this->gateway)) {
            $continer = new Container();
            $continer->set($this->gateway['class'], $this->gateway);
            $this->redis = $continer->get($this->gateway['class']);
        } else {
            $this->redis = Yii::$app->redis;
        }
    }

    /**
     * Executed on all swoole instances which subscribed rpc, e.g. barrage, chat, push notifacation..
     * @param $method
     * @param $param
     */
    public function run($method, $param, $id = 1)
    {
        if (!is_array($param)) throw new \Exception('Expected array for $param');
        return !!Yii::$app->redis->publish('rpc', $this->buildRpc($method, $param));
    }

    /**
     * Executed only once, even if multiple swoole instances subscribed, e.g. async sms send, some schedule task..
     * @param $method
     * @param $param
     */
    public function runOnce($method, $param, $id = 0)
    {
        if (!is_array($param)) throw new \Exception('Expected array for $param');
        if ($id === 0) $id = (int)microtime(1) * 1000;
        Yii::$app->redis->setex('rpc_con_lock_#' . $id, 600, 1);
        $param['__once'] = 1;
        return !!Yii::$app->redis->publish('rpc', $this->buildRpc($method, $param, $id));
    }

    /**
     * Build json string
     * @param $method
     * @param $param
     * @param int $id
     * @return string
     */
    private function buildRpc($method, $param, $id = 1)
    {
        $json = json_encode([
            'jsonrpc' => '2.0',
            'id' => $id,
            'method' => $method,
            'params' => $param
        ]);
        return $json;
    }

}