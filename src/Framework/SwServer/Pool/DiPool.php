<?php
/**
 * 容器对象池
 */

namespace Framework\SwServer\Pool;

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

    public function get($name)
    {
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
    use ComponentTrait, ServiceTrait, ContainerTrait;
}