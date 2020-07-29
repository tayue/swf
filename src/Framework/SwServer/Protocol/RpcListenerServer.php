<?php


namespace Framework\SwServer\Protocol;

use Framework\SwServer\Pool\DiPool;
use Framework\SwServer\Rpc\Server\Request;
use Framework\SwServer\Rpc\Server\Response;
use Framework\SwServer\ServerApplication;


class RpcListenerServer
{
    private $swoole_server;
    private $rpcPortListener;

    public function __construct($swooleServer, $listenServerConfig)
    {
        $this->swoole_server = $swooleServer;
        $this->rpcPortListener = $this->swoole_server->addlistener($listenServerConfig['host'], $listenServerConfig['port'], $listenServerConfig['sock_type']);
        $this->setting();
        $this->registerDefaultListenerEventCallback();
    }

    public function setting()
    {

        $this->rpcPortListener->set([

           // 'open_eof_check' => true,
            //'open_eof_split' => true,
            'package_eof'    => "\r\n\r\n",

        ]);
    }


    public function registerDefaultListenerEventCallback()
    {

        $this->rpcPortListener->on('Receive', array($this, 'onReceive'));
    }


    function onReceive($server, $fd, $reactor_id, $data)
    {
        try {
            $serverApp = DiPool::getInstance()->getSingleton(ServerApplication::class);
            $request = Request::new($server, $fd, $reactor_id, $data);
            $response = Response::new($server, $fd, $reactor_id);
            if ($request && $response) {
                $serverApp->rpcRun($request, $response);
            }
            return;
        } catch (\Throwable $e) {
            throw new \Exception($e->getMessage());
        }
    }


}