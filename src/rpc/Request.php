<?php
/**
 * Created by PhpStorm.
 * User: immusen
 * Date: 2019/1/23
 * Time: 10:53
 */

namespace immusen\websocket\src\rpc;

class Request
{

    private $id = 1;
    private $method;
    private $params;

    public function __construct($data)
    {
        if (!is_string($data))
            throw new Exception('Parse error', -32700, 'not well formed');
        $result = json_decode($data, true);
        if (!is_array($result))
            throw new Exception('Parse error', -32700);
        if (!isset($result['jsonrpc']) ||
            !isset($result['method']) ||
            !isset($result['id']) ||
            !isset($result['params'])
        ) throw new Exception('Invalid Request', -32600);
        if ($result['jsonrpc'] != '2.0')
            throw new Exception('Invalid Request', -32600);
        $this->id = $result['id'];
        $this->method = $result['method'];
        $this->params = $result['params'];
    }

    /**
     * @return int|mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }

}