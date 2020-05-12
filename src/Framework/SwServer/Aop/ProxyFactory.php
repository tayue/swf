<?php

namespace Framework\SwServer\Aop;
use Framework\SwServer\Annotation\ComposerHelper;

class ProxyFactory
{
    /**
     * @var array
     */
    private static $map = [];

    /**
     * @var Ast
     */
    protected $ast;

    public function __construct()
    {
        $this->ast = new Ast();
    }


    public function loadProxy(string $className, string $proxyClassName = ''): void
    {
        $dir = RUNTIME_PATH . '/container/proxy/';
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        $proxyFileName = str_replace('\\', '_', $className);
        $path = $dir . $proxyFileName . '.proxyAop.php';
        // If the proxy file does not exist, then try to acquire the coroutine lock.
        if (!file_exists($path)) {
            $targetPath = $path . '.' . uniqid();
            $code = $this->ast->proxy($className, $proxyClassName);
            file_put_contents($targetPath, $code);
            rename($targetPath, $path);
         }
        include_once $path;
    }
}
