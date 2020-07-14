<?php

namespace Framework\SwServer\Rpc;

use Framework\SwServer\Rpc\Exception\RpcException;
use Framework\SwServer\Rpc\Packet\AbstractPacket;
use Framework\SwServer\Rpc\Packet\JsonPacket;
use Framework\SwServer\Pool\DiPool;
use Framework\SwServer\Rpc\Contract\PacketInterface;

/**
 * Class Packet
 *
 *
 */
class Packet implements PacketInterface
{
    /**
     * Json packet
     */
    const JSON = 'JSON';

    /**
     * Packet type
     *
     * @var string
     */
    private $type = self::JSON;

    /**
     * Packet
     */
    private $packets = [];

    /**
     * @var bool
     */
    private $openEofCheck = true;

    /**
     * @var string
     */
    private $packageEof = "\r\n\r\n";

    /**
     * @var bool
     */
    private $openEofSplit = false;

    /**
     * @var AbstractPacket
     */
    private $packet;

    /**
     * @param Protocol $protocol
     *
     * @return string
     * @throws RpcException
     */
    public function encode(Protocol $protocol): string
    {
        $packet = $this->getPacket();
        return $packet->encode($protocol);
    }

    /**
     * @param string $string
     *
     * @return Protocol
     * @throws RpcException
     */
    public function decode(string $string): Protocol
    {
        $packet = $this->getPacket();
        return $packet->decode($string);
    }

    /**
     * @param mixed $result
     * @param int|null $code
     * @param string $message
     * @param null $data
     *
     * @return string
     * @throws RpcException
     */
    public function encodeResponse($result, int $code = null, string $message = '', $data = null): string
    {
        $packet = $this->getPacket();
        return $packet->encodeResponse($result, $code, $message, $data);
    }

    /**
     * @param string $string
     *
     * @return Response
     * @throws RpcException
     */
    public function decodeResponse(string $string): Response
    {
        $packet = $this->getPacket();
        return $packet->decodeResponse($string);
    }

    /**
     * @return array
     */
    public function defaultPackets(): array
    {
        return [
            self::JSON => DiPool::getInstance()->getSingleton(JsonPacket::class)
        ];
    }

    /**
     * @return bool
     */
    public function isOpenEofCheck(): bool
    {
        return $this->openEofCheck;
    }

    /**
     * @return string
     */
    public function getPackageEof(): string
    {
        return $this->packageEof;
    }

    /**
     * @return bool
     */
    public function isOpenEofSplit(): bool
    {
        return $this->openEofSplit;
    }

    /**
     * @return PacketInterface
     * @throws RpcException
     */
    private function getPacket(): PacketInterface
    {
        if (!empty($this->packet)) {
            return $this->packet;
        }

        $packets = array_merge($this->defaultPackets(), $this->packets);
        $packet = $packets[$this->type] ?? null;
        if (empty($packet)) {
            throw new RpcException(
                sprintf('Packet type(%s) is not supported!', $this->type)
            );
        }

        if (!$packet instanceof AbstractPacket) {
            throw new RpcException(
                sprintf('Packet type(%s) is not instanceof PacketInterface!', $this->type)
            );
        }

        $packet->initialize($this);
        $this->packet = $packet;
        return $packet;
    }
}