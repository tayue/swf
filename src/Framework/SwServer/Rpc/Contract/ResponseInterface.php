<?php


namespace Framework\SwServer\Rpc\Contract;


use Framework\SwServer\Rpc\Error;
use Swoole\Server;

/**
 * Class Rpc ResponseInterface
 *
 */
interface ResponseInterface
{

    /**
     * @param Error $error
     *
     * @return ResponseInterface
     */
    public function setError(Error $error): ResponseInterface;

    /**
     * @param $data
     *
     * @return ResponseInterface
     */
    public function setData($data): ResponseInterface;

    /**
     * @param string $content
     *
     * @return ResponseInterface
     */
    public function setContent(string $content): ResponseInterface;

    /**
     * @return bool
     */
    public function send(): bool;

    /**
     * @return Server
     */
    public function getServer(): Server;

    /**
     * @return int
     */
    public function getFd(): int;

    /**
     * @return int
     */
    public function getReactorId(): int;

    /**
     * @return mixed
     */
    public function getData();
}