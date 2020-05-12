<?php

namespace Framework\SwServer\Aop;

use Framework\SwServer\Annotation\AnnotationRegister;
use Framework\SwServer\Annotation\ComposerHelper;
use Framework\SwServer\Pool\DiPool;

class ProxyFactory
{
    /**
     * @var Ast
     */
    protected $ast;

    public function __construct()
    {
        $this->ast = new Ast();
    }


    public function loadProxy(string $className): void
    {
        $file = ComposerHelper::getClassLoader()->findFile($className);
        if (!$file) {
            return;
        }
        $sourceClassPath = realpath(dirname($file));
        if (!is_dir($sourceClassPath)) {
            return;
        }
        $sourceFileFixTime = filemtime($file);
        $proxyClassName = $className . "Aop";
        $path = ROOT_PATH . DIRECTORY_SEPARATOR . str_replace('\\', '/', $className) . "Aop.php";
        $targetFileFixTime = filemtime($path);
        if (!file_exists($path) || $sourceFileFixTime > $targetFileFixTime) {
            $code = $this->ast->proxy($className);
            file_put_contents($path, $code);
            DiPool::getInstance()->register($proxyClassName, [], true);
            AnnotationRegister::getInstance()->parseAnnotationForClass($proxyClassName);
        }
        include_once $path;
    }
}
