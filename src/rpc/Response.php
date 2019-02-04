<?php
/**
 * Created by PhpStorm.
 * User: immusen
 * Date: 2019/1/23
 * Time: 10:55
 */

namespace immusen\websocket\src\rpc;

use immusen\websocket\src\rpc\Exception;

class Response
{
    private $jsonrpc = "2.0";
    private $id = 1;
    private $result;
    private $error;

    public function __construct($id = 1)
    {
        $this->id = $id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param null $result
     */
    public function setResult($result)
    {
        $this->error = null;
        $this->result = $result;
    }

    /**
     * @param array $error
     */
    public function setError($error)
    {
        $this->result = null;
        if (!is_array($error) ||
            !isset($error['code']) ||
            !isset($error['message'])
        ) {
            $this->error = [
                'code' => '-32603',
                'message' => 'INTERNAL_ERROR',
            ];
        } else {
            $this->error = $error;
        }
    }

    public function serialize()
    {
        $data = [
            'jsonrpc' => $this->jsonrpc,
            'id' => $this->id
        ];
        if ($this->error !== null) {
            $data['error'] = $this->error;
        } else {
            $data['result'] = $this->result;
        }
        return json_encode($data);
    }


}