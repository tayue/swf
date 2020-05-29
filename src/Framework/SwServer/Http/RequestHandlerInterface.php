<?php


namespace Framework\SwServer\Http;

use Swoole\Http\Request;
use Swoole\Http\Response;

interface RequestHandlerInterface
{
    public function handle(Request $request): Response;
}