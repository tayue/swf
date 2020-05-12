<?php
namespace Framework\SwServer\Guzzle;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Framework\SwServer\Pool\DiPool;
use Swoole\Coroutine;

class ClientFactory
{
    private $container;

    public function __construct(DiPool $container)
    {
        $this->container = $container;
    }

    public function create(array $options = []): Client
    {
        $stack = null;
        if (Coroutine::getCid() > 0) {
            $stack = HandlerStack::create(new CoroutineHandler());
        }

        $config = array_replace(['handler' => $stack], $options);

        if (method_exists($this->container, 'register')) {
            // Create by DI for AOP.
            return $this->container->register(Client::class, ['config' => $config]);
        }
        return new Client($config);
    }
}
