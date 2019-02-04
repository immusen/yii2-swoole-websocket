<?php
/**
 * Created by PhpStorm.
 * User: immusen
 * Date: 2019/1/23
 * Time: 10:51
 */

namespace immusen\websocket\src\rpc;

class Exception extends \Exception
{
    const PARSE_ERROR = -32700;
    const INVALID_REQUEST = -32600;
    const METHOD_NOT_FOUND = -32601;
    const INVALID_PARAMS = -32602;
    const INTERNAL_ERROR = -32603;

    private $data;

    public function __construct($message = "", $code = 0, $data = null)
    {
        $this->data = $data;
        parent::__construct($message, $code);
    }

    public function getData()
    {
        return $this->data;
    }

    public function getError()
    {

        $result = [
            "code" => $this->getCode(),
            "message" => $this->getMessage(),
        ];
        if ($this->data !== null)
            $result['data'] = $this->data;
        return $result;
    }
}