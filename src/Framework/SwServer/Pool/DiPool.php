<?php
/**
 * 容器对象池
 */

namespace Framework\SwServer\Pool;

use Framework\SwServer\Annotation\AnnotationRegister;
use Framework\SwServer\Aop\AopProxyFactory;
use Framework\Traits\ComponentTrait;
use Framework\Traits\ContainerTrait;
use Framework\Traits\ServiceTrait;

class DiPool
{
    private static $instance;

    private function __construct($args = [])
    {
        $this->init($args);
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    public static function getInstance($args = [])
    {
        if (!isset(self::$instance)) {
            self::$instance = new self($args);
        }
        return self::$instance;
    }

    public function register(string $class, array $params = [], $isForceInstance = false)
    {
        $object = $this->registerObject($class, ['class' => $class], $params, $isForceInstance);
        return $object;
    }

    public function registerSingletonByObject(string $class, $object)
    {
        $object = $this->setSingletonByObject($class, $object);
        return $object;
    }

    public function registerService(string $com_alias_name, $classNamespace)
    {
        $object = $this->createServiceObject($com_alias_name, ['class' => $classNamespace]);
        return $object;
    }

    public function registerComponent(string $com_alias_name, $classNamespace)
    {
        $object = $this->createComponentObject($com_alias_name, ['class' => $classNamespace]);
        return $object;
    }

    public function init($args = [])
    {
        $this->initComponents();
        $this->initServices();
    }

    public function checkGetProxy($name)
    {
        $res=false;
        $getSourceClassName='';
        if (isset($this->_components[$name])) {
            $getSourceClassName = $this->_components[$name];
        } elseif (isset($this->_services[$name])) {
            $getSourceClassName = $this->_services[$name];
        } else {
            class_exists($name) && $getSourceClassName = $name;
        }
        if(!$getSourceClassName){
            return $res;
        }
        if($this->register(AopProxyFactory::class)->getProxyClassName($getSourceClassName)){
            return $this->checkInitAopProxyClass($getSourceClassName);
        }
        return $res;
    }

    public function get($name)
    {
        if($proxyClassObj=$this->checkGetProxy($name)){ //如果发现存在代理类那么优先取代理类实例
           return $proxyClassObj;
        }
        if ($componentObject = $this->getComponent($name)) {
            if ($componentObject) {
                return $componentObject;
            } else {
                $this->clearComponent($name);
                return false;
            }
        } else if ($serviceObject = $this->getService($name)) {
            if ($serviceObject) {
                return $serviceObject;
            } else {
                $this->clearService($name);
                return false;
            }
        } else if ($singletonObject = $this->getSingleton($name)) {
            if ($singletonObject) {
                return $singletonObject;
            }
            return false;
        }
    }

    //根据注解生成aop代理类
    public function initAspectAopAnnotationClass()
    {
        $aspectAns = AnnotationRegister::getAspectAnnotations();
        foreach ($aspectAns as $aspectKey => $eachAspectAn) {
            @list($className, $methodName) = explode("::", $aspectKey);
            if (AnnotationRegister::checkIsHasAspectAnnotation($className, $methodName)) {
                echo "{$className} has aspect annotation #########\r\n";
                $this->checkInitAopProxyClass($className);
            }
        }
    }

    public function checkInitAopProxyClass($className)
    {
        $proxyClassName = $this->register(AopProxyFactory::class)->checkGetProxy($className);
        //检查此代理类是否在容器中初始化过
        if ($this->isSetSingleton($proxyClassName)) {
            echo "{$proxyClassName} already set singleton !!\r\n";
            return $this->getSingleton($proxyClassName);
        }
        //通过类名获取
        $workClass_by_classname = new \ReflectionClass($proxyClassName);
        $proxyClassObj = $workClass_by_classname->newInstance();
        $getProperties = $workClass_by_classname->getProperties();
        $sourceObj = $this->getSingleton($className);
        foreach ($getProperties as $eachProperty) {
            $propertyName = $eachProperty->name;
            if (property_exists($sourceObj, $propertyName)) {
                $getPropertyMethodStr = 'get' . ucfirst($propertyName);
                if (method_exists($sourceObj, $getPropertyMethodStr)) {
                    $eachProperty->setAccessible(true);
                    $eachProperty->setValue($proxyClassObj, $sourceObj->$getPropertyMethodStr());
                }
            }
        }
        $proxyClassObj && $this->setSingletonByObject($proxyClassName, $proxyClassObj);
        return $proxyClassObj;
    }

    use ComponentTrait, ServiceTrait, ContainerTrait;
}