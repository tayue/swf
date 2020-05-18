<?php


namespace Framework\SwServer\Aop;


use Framework\SwServer\Annotation\ComposerHelper;


class AopProxyFactory extends ProxyFactory
{

    /**
     * @var array
     */
    private static $map = [];

    public function checkGetProxy($className)
    {
        $baseClassDirName = dirname(str_replace('\\', '/', $className));
        $baseClassDirName = str_replace('/', '\\', $baseClassDirName);
        $proxyIdentifier = basename(str_replace('\\', '/', $className)) . "_" . md5($className);
        $this->loadProxy($className);
        static::$map[$className] = $baseClassDirName . '\\' . $proxyIdentifier;
        return static::$map[$className];
    }

    public function getMap()
    {
        return static::$map;
    }

    public function getProxyClassName($className)
    {
        $proxyClassName='';
        if(isset(static::$map[$className])){
            $proxyClassName= static::$map[$className];
        }
        return $proxyClassName;
    }

    public function loadProxy(string $className): void
    {
        $dir = ROOT_PATH . '/runtime/container/proxy/';
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        $proxyFileName = str_replace('\\', '_', $className);
        $path = $dir . $proxyFileName . '.proxy.php';
        $file = ComposerHelper::getClassLoader()->findFile($className);
        if (!$file) {
            return;
        }
        $sourceClassPath = realpath(dirname($file));
        if (!is_dir($sourceClassPath)) {
            return;
        }
        $sourceFileFixTime = filemtime($file);
        $targetFileFixTime = filemtime($path);
        if (!file_exists($path) || $sourceFileFixTime > $targetFileFixTime) {
            $targetPath = $path . '.' . uniqid();
            $code = $this->ast->proxy($className);
            $code && file_put_contents($targetPath, $code);
            rename($targetPath, $path);
        }
        include_once $path;
    }

}