<?php


namespace Framework\SwServer\Rpc\Server;


use ReflectionException;
use Framework\SwServer\Pool\DiPool;
use Framework\Traits\PrototypeTrait;
use Swoft\Bean\Exception\ContainerException;
use Swoft\Rpc\Exception\RpcException;
use Swoft\Rpc\Packet;
use Framework\SwServer\Rpc\Contract\RequestInterface;
use Swoole\Server;

/**
 * Class Request
 *
 */
class Request implements RequestInterface
{
    use PrototypeTrait;

    /**
     * Router handler attribute
     */
    public const ROUTER_ATTRIBUTE = 'swoftRouterHandler';

    /**
     * @var string
     */
    protected $version = '';

    /**
     * @var string
     */
    protected $interface = '';

    /**
     * @var string
     */
    protected $method = '';

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @var array
     */
    protected $ext = [];

    /**
     * Raw data
     *
     * @var string
     */
    protected $data = '';

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
     * @var float
     */
    protected $requestTime = 0;

    /**
     * @var array
     *
     * @example
     * [
     *    'key' => value,
     *    'key' => value,
     * ]
     */
    protected $attributes = [];

    /**
     * @param Server $server
     * @param int    $fd
     * @param int    $reactorId
     * @param string $data
     *
     * @return Request
     * @throws RpcException
     */
    public static function new(
        Server $server = null,
        int $fd = null,
        int $reactorId = null,
        string $data = null
    ): self {
        $instance = self::__instance();

        /* @var Packet $packet */
        $packet   = DiPool::getInstance()->getService('rpcServerPacket');
        $protocol = $packet->decode($data);
        $instance->version     = $protocol->getVersion();
        $instance->interface   = $protocol->getInterface();
        $instance->method      = $protocol->getMethod();
        $instance->params      = $protocol->getParams();
        $instance->ext         = $protocol->getExt();
        $instance->data        = $data;
        $instance->server      = $server;
        $instance->reactorId   = $reactorId;
        $instance->fd          = $fd;
        $instance->requestTime = microtime(true);

        return $instance;
    }

    /**
     * @return array
     *
     */
    public function getParamsMap(): array
    {

        return [];
    }

    /**
     * @param array $params
     *
     * @return RequestInterface
     */
    public function withParams(array $params): RequestInterface
    {
        $clone = clone $this;

        $clone->params = $params;
        return $clone;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getInterface(): string
    {
        return $this->interface;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @return array
     */
    public function getExt(): array
    {
        return $this->ext;
    }

    /**
     * @param string     $key
     * @param mixed|null $default
     *
     * @return mixed|null
     */
    public function getExtKey(string $key, $default = null)
    {
        return $this->ext[$key] ?? $default;
    }

    /**
     * @param int        $index
     * @param mixed|null $default
     *
     * @return mixed|null
     */
    public function getParam(int $index, $default = null)
    {
        return $this->params[$index] ?? $default;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
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
     * @return float
     */
    public function getRequestTime(): float
    {
        return $this->requestTime;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function setAttribute(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }

    /**
     * @param string $key
     * @param null   $default
     *
     * @return mixed
     */
    public function getAttribute(string $key, $default = null)
    {
        return $this->attributes[$key] ?? $default;
    }
}