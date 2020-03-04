<?php


namespace Framework\SwServer\Grpc;

/**
 * Represents an active call that sends a single message and then gets a
 * stream of responses.
 */
class ServerStreamingCall extends StreamingCall
{

    public function send($message = null): bool
    {
        if (!$this->streamId) {
            $this->streamId = $this->client->openStream(
                $this->method,
                Parser::serializeMessage($message)
            );
            return $this->streamId > 0;
        } else {
            trigger_error('ServerStreamingCall can only send once!', E_USER_ERROR);
            return false;
        }
    }

    public function push($message): bool
    {
        trigger_error('ServerStreamingCall can not push data by client!', E_USER_ERROR);
        return false;
    }

}
