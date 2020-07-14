<?php


namespace Framework\SwServer\Rpc\Server;


use Framework\Traits\PrototypeTrait;
use Framework\SwServer\Rpc\Error;
use Framework\SwServer\Rpc\Exception\RpcException;
use Framework\SwServer\Rpc\Contract\ResponseInterface;
use Framework\SwServer\Pool\DiPool;
use Swoole\Server;

/**
 * Class Response
 *
 *
 */
class Response implements ResponseInterface
{
    use PrototypeTrait;

    /**
     * @var Server
     */
    protected $server;

    /**
     * @var int
     */
    protected $fd = 0;

    /**
     * @var int
     */
    protected $reactorId = 0;

    /**
     * @var string
     */
    protected $content = '';

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var Error
     */
    protected $error;

    /**
     * @param Server $server
     * @param int $fd
     * @param int $reactorId
     *
     * @return Response
     */
    public static function new(Server $server = null, int $fd = null, int $reactorId = null): self
    {
        $instance = self::__instance();

        $instance->server = $server;
        $instance->reactorId = $reactorId;
        $instance->fd = $fd;

        return $instance;
    }

    /**
     * @param Error $error
     *
     * @return ResponseInterface
     */
    public function setError(Error $error): ResponseInterface
    {
        $this->error = $error;
        return $this;
    }

    /**
     * @param $data
     *
     * @return ResponseInterface
     */
    public function setData($data): ResponseInterface
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @param string $content
     *
     * @return ResponseInterface
     */
    public function setContent(string $content): ResponseInterface
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return bool
     * @throws RpcException
     */
    public function send(): bool
    {
        $this->prepare();
        return $this->server->send($this->fd, $this->content);
    }

    /**
     * @return Server
     */
    public function getServer(): Server
    {
        return $this->server;
    }

    /**
     * @return int
     */
    public function getFd(): int
    {
        return $this->fd;
    }

    /**
     * @return int
     */
    public function getReactorId(): int
    {
        return $this->reactorId;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    protected function prepare(): void
    {
        $packet = DiPool::getInstance()->getService('rpcServerPacket');
        if ($this->error === null) {
            $this->content = $packet->encodeResponse($this->data);
            return;
        }
        $code = $this->error->getCode();
        $message = $this->error->getMessage();
        $data = $this->error->getData();
        $this->content = $packet->encodeResponse(null, $code, $message, $data);
    }
}