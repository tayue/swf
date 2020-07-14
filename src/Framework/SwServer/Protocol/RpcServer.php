<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/11/23
 * Time: 10:26
 */

namespace Framework\SwServer\Protocol;
use Framework\SwServer\BaseServer;
use Framework\SwServer\Pool\DiPool;
use Framework\Tool\Log;
use Framework\SwServer\ServerApplication;
use Framework\SwServer\ServerManager;
use Framework\SwServer\DataPackage\Pack;
use Framework\SwServer\Rpc\Server\Request;
use Framework\SwServer\Rpc\Server\Response;

class RpcServer extends BaseServer
{

    public $fd;

    public function __construct($config)
    {
        parent::__construct($config);
        self::$isWebServer = false;
        $this->setSwooleSockType();
    }

    public function createServer()
    {
        self::$server = new \swoole_server($this->config['server']['listen_address'], $this->config['server']['listen_port'], self::$swoole_process_mode, self::$swoole_socket_type);
        self::$server->set($this->setting);
        Log::getInstance()->setConfig($this->config);
        $this->setLogger(Log::getInstance());
        return self::$server;
    }

    public function getServer()
    {
        return self::$server;
    }

    public function onMasterStart()
    {

    }

    function onStart($server)
    {
        echo "RpcServer onStart\r\n";
    }

    function onWorkerStart($server, $worker_id)
    {
        //初始化应用层
        $app = new ServerApplication($this->config);
        ServerManager::$serverApp = $app;
    }

    function onConnect($server, $client_id, $from_id)
    {

    }

    function onReceive($server, $fd, $reactor_id, $data)
    {
        try {
            $request  = Request::new($server, $fd, $reactor_id, $data);
            $response = Response::new($server, $fd, $reactor_id);
            if ($request && $response) {
                $serverApp = ServerManager::$serverApp;
                $serverApp->rpcRun($request, $response);
            }
            ServerManager::destroy(); //销毁应用实例
            return;
        } catch (\Throwable $e) {
            throw new \Exception($e->getMessage());
        }
    }

    function onClose($server, $client_id, $from_id)
    {
        // TODO: Implement onClose() method.
    }

    function onShutdown($server)
    {

    }


    function onFinish(\swoole_server $serv, $task_id, $data)
    {
        echo "Task#$task_id finished, info=" . $data . PHP_EOL;
    }



}