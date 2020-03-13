<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2020/03/11
 * Time: 10:26
 */

namespace Framework\SwServer\Protocol;

use Framework\SwServer\BaseServer;
use Framework\Tool\Log;
use Framework\SwServer\ServerApplication;
use Framework\SwServer\ServerManager;
use Framework\Core\error\CustomerError;

use App\Protobuf\HelloReply;
use App\Protobuf\HelloRequest;
use Framework\SwServer\Grpc\Parser;
use Framework\SwServer\Grpc\Client;


class GrpcServer extends WebServer
{

    const POST_MAXSIZE = 2000000; //POST最大2M
    public $fd;
    public $streamId;


    public function __construct($config)
    {
        //grpc server 需开启 http2 协议
        $config['server']['setting']['open_http2_protocol']=true;
        parent::__construct($config);
    }


    function onStart($server)
    {
        echo "GrpcServer onStart\r\n";
    }


    function onRequest(\swoole_http_request $request, \swoole_http_response $response)
    {
        try {
            if ($request->server['path_info'] == '/favicon.ico') {
                $response->end('');
                return;
            }
            if ($request->server['request_uri'] == Client::CLOSE_KEYWORD) {
                $response->end(Client::CLOSE_KEYWORD);
                return;
            }
            $this->fd = $request->fd;
            $this->streamId = $request->streamId;
            if ($request->server['request_uri']) { //请求地址
                $serverApp = \unserialize(ServerManager::$serverApp);
                $serverApp->grpcRun($this->fd, $request, $response);
            }
            ServerManager::destroy(); //销毁应用实例
        } catch (\Throwable $t) {
            CustomerError::writeErrorLog($t);
        }
    }


}
