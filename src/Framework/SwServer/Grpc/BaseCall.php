<?php

namespace Framework\SwServer\Grpc;

class BaseCall
{
    /** @var Client */
    protected $client;
    /** @var string */
    protected $method;
    /** @var mixed */
    protected $deserialize;
    /** @var int */
    protected $streamId;

    public function setClient($client)
    {
        $this->client = $client;
    }

    public function setMethod(string $method)
    {
        $this->method = $method;
    }

    public function setDeserialize($deserialize)
    {
        $this->deserialize = $deserialize;
    }

    public function getStreamId(): int
    {
        return $this->streamId;
    }

    public function setStreamId(int $streamId)
    {
        $this->streamId = $streamId;
    }

}