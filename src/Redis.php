<?php
/**
 * Created by PhpStorm.
 * User: immusen
 * Date: 2019/01/22
 * Time: 11:01 AM
 */

namespace immusen\websocket\src;

/**
 * Beta
 * Class Redis Connect Pool
 * @package immusen\websocket\src
 */
class Redis
{
    private $pool;
    private $config;
    public static $redis;

    private function __construct()
    {
        $this->pool = [];
        $this->config = \Yii::$app->params['redis'];
        if (!isset($this->config['pool_size']))
            $this->config['pool_size'] = 10;
        $this->openConnection($this->config['pool_size']);
    }

    public static function getRedis()
    {
        if (!self::$redis)
            self::$redis = new self();
        return self::$redis;
    }

    public function openConnection($size = 1)
    {
        for ($i = 0; $i < $size; $i++) {
            $redis = $this->newConnection();
            array_push($this->pool, $redis);
        }
    }

    public function newConnection()
    {
        //depend php-redis extention
        $redis = new \Redis();
        $res = $redis->connect($this->config['host'], $this->config['port']);
        if ($res == false)
            throw new \Exception('failed to connect redis server.', 999);
        if (isset($this->config['auth']) && !$redis->auth($this->config['auth']))
            throw new \Exception('Redis auth failed!');
        return $redis;
    }

    public function getConnection()
    {
        $attempt = 10;
        do {
            $attempt--;
            $connection = array_shift($this->pool);
            if ($connection) break;
            else
                if ($attempt == 0) throw new \Exception('failed to increase redis pool', 999);
            echo '#increase redis pool' . PHP_EOL;
            $this->openConnection();
        } while ($attempt);
        return $connection;
    }

    public function releaseConnection($connection)
    {
        array_push($this->pool, $connection);
    }

    public function __call($name, $arguments)
    {
        $redis = $this->getConnection();
        try {
            $result = call_user_func_array([$redis, $name], $arguments);
            $this->releaseConnection($redis);
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            $result = false;
        }
        return $result;
    }

}