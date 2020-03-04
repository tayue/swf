<?php

namespace Framework\SwServer\Grpc;

/**
 * Represents an active call that sends a stream of messages and then gets
 * a single response.
 */
class ClientStreamingCall extends StreamingCall
{
    private $received = false;

    public function recv(float $timeout = GRPC_DEFAULT_TIMEOUT)
    {
        if (!$this->received) {
            $this->received = true;
            return parent::recv($timeout);
        }
        trigger_error('ClientStreamingCall can only recv once!', E_USER_ERROR);
        return false;
    }

}
