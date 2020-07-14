<?php


namespace Framework\SwServer\Rpc\Contract;

use Framework\SwServer\Rpc\Error;
use Framework\SwServer\Rpc\Protocol;
use Framework\SwServer\Rpc\Response;

/**
 * Class PacketInterface
 *
 * @since 2.0
 */
interface PacketInterface
{
    /**
     * @param Protocol $protocol
     *
     * @return string
     */
    public function encode(Protocol $protocol): string;

    /**
     * @param string $string
     *
     * @return Protocol
     */
    public function decode(string $string): Protocol;

    /**
     * @param mixed  $result
     * @param int    $code
     * @param string $message
     * @param Error  $data
     *
     * @return string
     */
    public function encodeResponse($result, int $code = null, string $message = '', $data = null): string;

    /**
     * @param string $string
     *
     * @return Response
     */
    public function decodeResponse(string $string): Response;
}