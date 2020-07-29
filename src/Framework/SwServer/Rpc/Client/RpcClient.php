<?php


namespace Framework\SwServer\Rpc\Client;

use Swoole\Coroutine\Client;

use Framework\Traits\PrototypeTrait;
use Framework\SwServer\Pool\DiPool;
use Exception;

class RpcClient
{
    use PrototypeTrait;
    private $package = null;
    private $setting = [];
    private $connection = null;
    private $config = [];

    public function __construct($config, $setting = [])
    {
        $this->config = $config;
        $this->setting = $setting;
        $this->package = DiPool::getInstance()->getService('rpcServerPacket');
        $this->create();
    }

    public function create()
    {
        $this->connection = new Client(SWOOLE_SOCK_TCP);
        [$host, $port] = $this->parseGetHostPort();
        if (!$this->connection->connect($host, $port)) {
            throw new Exception("failed to connect host:{$host}, port:{$port} rpc client.");
        }
        $this->getSetting();
        $this->connection->set($this->setting);
        return $this->connection;
    }

    private function parseGetHostPort()
    {
        if (!isset($this->config['clients']) || !$this->config['clients'] || !is_array($this->config['clients'])) {
            throw new Exception("clients config error!");
        }
        $randIndex = array_rand($this->config['clients'], 1);
        $hostPort = explode(':', $this->config['clients'][$randIndex]);
        [$host, $port] = $hostPort;
        return [$host, $port];
    }

    public function getPackage()
    {
        return $this->package;
    }

    public function isConnected()
    {
        return $this->connection->isConnected();
    }

    public function close()
    {
        $this->connection->close();
    }

    public function getClient(): Client
    {
        return $this->connection;
    }

    public function reconnect()
    {
        $this->create();
        return true;
    }

    public function send(string $data)
    {
        return (bool)$this->connection->send($data);
    }

    public function recv()
    {
        return $this->connection->recv();
    }

    /**
     * @return array
     */
    private function defaultSetting(): array
    {
        return [
            'open_eof_check' => true,
            'open_eof_split' => true,
            'package_eof' => "\r\n\r\n",
        ];
    }

    private function getSetting()
    {
        $this->setting && $this->setting = array_merge($this->defaultSetting(), $this->setting);
    }


}