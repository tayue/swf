<?php
/**
 * Created by PhpStorm.
 * User: hdeng
 * Date: 2018/12/28
 * Time: 17:09
 */

namespace Framework\SwServer;
use Framework\SwServer\Http\HttpJoinPoint;
use Framework\SwServer\Http\PipelineHttpHandleAop;
use Framework\SwServer\Coroutine\CoroutineManager;
use Framework\SwServer\Pool\DiPool;
use App\Middleware\TraceMiddleware;

class ServerApplication extends AbstractServerApplication
{
    public function run($fd, \swoole_http_request $request, \swoole_http_response $response)
    {
        $this->fd = $fd;
        $this->init();
        CoroutineManager::set('tracer.request',$request);
        CoroutineManager::set('tracer.response',$response);

        $httpJoinPoint=new HttpJoinPoint(function ()  {
            $request = CoroutineManager::get('tracer.request');
            $response = CoroutineManager::get('tracer.response');
            ServerManager::getApp()->request=$request;
            ServerManager::getApp()->response=$response;
            return $this->parseUrl($request, $response);
        });
        $this->httpMiddlewares=[];
        $pipeline=DiPool::getInstance()->register(PipelineHttpHandleAop::class);
        return $pipeline->via('process')
            ->through($this->httpMiddlewares)
            ->send($httpJoinPoint)
            ->then(function (HttpJoinPoint $proceedingJoinPoint) {
                return $proceedingJoinPoint->processOriginalMethod();
            });
    }

    public function tcpRun($fd, $recv)
    {
        $this->fd = $fd;
        $this->init();
        $this->parseTcpRoute($recv);
    }

    public function webSocketRun($fd, $messageData)
    {
        $this->fd = $fd;
        $this->init();
        $this->parseRoute($messageData);
    }

    public function grpcRun($fd, \swoole_http_request $request, \swoole_http_response $response)
    {
        $this->fd = $fd;
        $this->init();
        CoroutineManager::set('tracer.request',$request);
        CoroutineManager::set('tracer.response',$response);
        ServerManager::getApp()->request=$request;
        ServerManager::getApp()->response=$response;
        $this->parseUrl($request, $response,true);
    }
}