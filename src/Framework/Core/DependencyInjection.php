<?php
/**
 * php利用反射特性模拟java类依赖注入
 */

namespace Framework\Core;

use Framework\SwServer\Pool\DiPool;
use Google\Protobuf\Internal\Message;
use Framework\SwServer\Grpc\Parser;

class DependencyInjection
{
    public static $currentInstance;

    public static function make($className, $methodName, $params = [])
    {
        // 获取类的实例
        self::$currentInstance = self::getInstance($className);
        // 执行初始化方法
        self::checkInitFunc();
        // 获取该方法所需要依赖注入的参数
        $paramArr = self::resolveClassMethodDependencies($className, $methodName);
        return self::$currentInstance->{$methodName}(...array_merge($paramArr, $params));
    }

    public static function checkInitFunc(){
        $controllerInstance = new \ReflectionClass(self::$currentInstance);
        //检测控制器初始化方法
        if ($controllerInstance->hasMethod('init')) {
            $initMethod = new \ReflectionMethod(self::$currentInstance, 'init');
            if ($initMethod->isPublic()) {
                $initMethod->invoke(self::$currentInstance);
            }
        }
    }

    public static function grpcMake($className, $methodName, $rawContent,$params = [])
    {
        // 获取类的实例
        self::$currentInstance = self::getInstance($className);
        // 获取该方法所需要依赖注入的参数
        $paramArr = self::resolveGrpcClassMethodDependencies($className, $rawContent,$methodName);
        if(!$paramArr){
            return [];
        }
        return self::$currentInstance->{$methodName}(...array_merge($paramArr, $params));
    }

    public static function getInstance($className)
    {
        $paramArr = self::resolveClassMethodDependencies($className);
        return DiPool::getInstance()->registerObject($className, ['class' => $className], $paramArr);
    }

    public static function resolveClassMethodDependencies($className, $method = '__construct')
    {
        $parameters = []; // 记录参数，和参数类型
        if (!\method_exists($className, $method)) {
            return $parameters;
        }
        // 获得构造函数
        $reflector = new \ReflectionMethod($className, $method);
        if (count($reflector->getParameters()) <= 0) {
            return $parameters;
        }
        foreach ($reflector->getParameters() as $key => $parameter) {
            $currentParamsReflectionClass = $parameter->getClass();
            if ($currentParamsReflectionClass) {
                // 获得参数类型名称
                $paramClassName = $currentParamsReflectionClass->getName();
                $paramClassParams = self::resolveClassMethodDependencies($paramClassName);
                $parameters[] = DiPool::getInstance()->registerObject($paramClassName, ['class' => $paramClassName], $paramClassParams);
            }

        }
        return $parameters;
    }

    public static function resolveGrpcClassMethodDependencies($className,$rawContent, $method = '__construct')
    {
        $parameters = []; // 记录参数，和参数类型
        if (!\method_exists($className, $method)) {
            return $parameters;
        }
        // 获得构造函数
        $reflector = new \ReflectionMethod($className, $method);
        if (count($reflector->getParameters()) != 1) { //grpc Action里方法参数必须有一个Grpc请求对象参数
            return $parameters;
        }
        foreach ($reflector->getParameters() as $key => $parameter) {
            $currentParamsReflectionClass = $parameter->getClass();
            if ($currentParamsReflectionClass) {
                // 获得参数类型名称
                $paramClassName = $currentParamsReflectionClass->getName();
                $currentObject = DiPool::getInstance()->registerObject($paramClassName, ['class' => $paramClassName], []);
                if ($currentObject instanceof Message) {
                    $request_message = Parser::deserializeMessage([$paramClassName, null], $rawContent);
                    $parameters[] = $request_message;
                }

            }
        }
        return $parameters;
    }


}

