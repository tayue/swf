<?php
/**容器抽象类
 * Class AbstractContainer
 */

namespace Framework\SwServer\Pool;

use Exception;
use Psr\Container\ContainerInterface;
use ReflectionMethod;


abstract class BaseContainer implements ContainerInterface
{
    protected $resolvedEntries = [];
    protected $_singletons = [];
    public $_params = [];
    /**
     * @var array
     */
    protected $definitions = [];

    public function getSingletons()
    {
        return $this->_singletons;
    }

    public function get($id)
    {
        if (!$this->has($id)) {
            throw new Exception("No entry or class found for {$id}");
        }
        $instance = $this->make($id);
        return $instance;
    }

    public function has($id)
    {
        return isset($this->definitions[$id]);
    }

    /**获取类构造方法参数依赖
     * @param $className
     * @param string $method
     * @return array
     * @throws \ReflectionException
     */
    public function resolveClassMethodDependencies($className, $method)
    {
        $parameters = []; // 记录参数，和参数类型
        if (!$className || !$method) {
            return $parameters;
        }
        if (!\method_exists($className, $method)) {
            return $parameters;
        }
        // 获得构造函数
        $reflector = new ReflectionMethod($className, $method);
        if (count($reflector->getParameters()) <= 0) {
            return $parameters;
        }
        foreach ($reflector->getParameters() as $key => $parameter) {
            $currentParamsReflectionClass = $parameter->getClass();
            if ($currentParamsReflectionClass) {
                // 获得参数类型名称
                $paramClassName = $currentParamsReflectionClass->getName();
                $paramClassParams = $this->resolveClassMethodDependencies($paramClassName);
                $definitions = ['class' => $paramClassName];
                if ($paramClassParams) {
                    $definitions['constructor'] = $paramClassParams;
                }
                $parameters[] = $this->registerObject($paramClassName, $definitions);
            }

        }
        return $parameters;
    }

    /**注册对象
     * @param $paramClassName
     * @param $definitions
     * @return mixed|object
     * @throws Exception
     */
    public function registerObject($paramClassName, $definitions)
    {
        if (isset($this->_singletons[$paramClassName])) {
            return $this->_singletons[$paramClassName];
        } else {
            return $this->build($paramClassName, $definitions);
        }
    }

    /**构建容器对象通过定义参数
     * @param $id
     * @param $definitions
     * @return mixed|object
     * @throws Exception
     */
    public function build($id, $definitions)
    {
        $this->injection($id, $definitions);
        return $this->make($id);
    }

    /**生成对象实例
     * @param $name
     * @return mixed|object
     * @throws \ReflectionException
     */
    public function make($name)
    {
        if (!is_string($name)) {
            throw new \InvalidArgumentException(sprintf(
                'The name parameter must be of type string, %s given',
                is_object($name) ? get_class($name) : gettype($name)
            ));
        }

        if (isset($this->resolvedEntries[$name])) {
            return $this->resolvedEntries[$name];
        }

        if (!$this->has($name)) {
            throw new Exception("No entry or class found for {$name}");
        }
        $definition = $this->definitions[$name];
        $params = [];
        if (is_array($definition) && isset($definition['class']) && $definition['class']) {
            isset($definition['constructor']) && $params = $definition['constructor'];
            unset($definition['constructor']);
            $class = $definition['class'];
            unset($definition['class']);
            $this->_params[$class] = $params;
        } else {
            throw new Exception("{$name} params defined error!");
        }
        $object = $this->reflector($class, $params);
        if ($class && $object) $this->setSingletonByObject($class, $object);
        return $this->resolvedEntries[$name] = $object;
    }

    /**获取单例对象
     * @param $className
     * @param array $constructorParams
     * @return bool|mixed|object
     * @throws Exception
     */
    public function getSingleton($className, $constructorParams = [])
    {
        if (isset($this->_singletons[$className])) {
            return $this->_singletons[$className];
        } else {
            if (!class_exists($className)) {
                return false;
            }
            $definition = ['class' => $className];
            if ($constructorParams) {
                $definition['constructor'] = $constructorParams;
            }
            return $this->build($className, $definition);
        }
        return false;
    }

    public function isSetSingleton($className)
    {
        $flag = false;
        if (isset($this->_singletons[$className])) {
            $flag = true;
        }
        return $flag;
    }


    /**设置单例对象
     * @param $class
     * @param $object
     * @return mixed
     */
    public function setSingletonByObject($class, $object)
    {
        if (isset($this->_singletons[$class])) unset($this->_singletons[$class]);
        class_exists($class) && $object && $this->_singletons[$class] = $object;
        return $this->_singletons[$class];
    }

    /**获取类的反射依赖
     * @param $concrete
     * @param array $params
     * @return mixed|object
     * @throws \ReflectionException
     */
    public function reflector($concrete, array $params = [])
    {
        if ($concrete instanceof \Closure) {
            return $concrete($params);
        } elseif (is_string($concrete)) {
            $reflection = new \ReflectionClass($concrete);
            //获取该类的构造方法参数依赖（如果该类的参数为依赖类将其实例化）
            $dependencies = $this->getDependencies($reflection);
            foreach ($params as $index => $value) {
                $dependencies[$index] = $value;
            }
            //将依赖的构造参数重新赋值并产生一个该类的实例对象
            return $reflection->newInstanceArgs($dependencies);
        } elseif (is_object($concrete)) {
            return $concrete;
        }
    }

    /**如果该类的参数为依赖类将其实例化
     * @param \ReflectionClass $reflection
     * @return array
     */
    private function getDependencies($reflection)
    {
        $dependencies = [];
        $constructor = $reflection->getConstructor();
        if ($constructor !== null) {
            $parameters = $constructor->getParameters();
            $dependencies = $this->getParametersByDependencies($parameters);
        }

        return $dependencies;
    }

    /**
     *
     * 获取构造类相关参数的依赖
     * @param array $dependencies
     * @return array $parameters
     * */
    private function getParametersByDependencies(array $dependencies)
    {
        $parameters = [];

        foreach ($dependencies as $param) {
            $paramName = $param->getName();
            if ($param->getClass()) {
                $paramClassName = $param->getClass()->name;
                $paramObject = $this->reflector($paramClassName);
                if ($paramName && $paramObject) $this->setSingletonByObject($paramClassName, $paramObject);
                $parameters[] = $paramObject;
            } elseif ($param->isArray()) {
                if ($param->isDefaultValueAvailable()) {
                    $parameters[$paramName] = $param->getDefaultValue();
                } else {
                    $parameters[$paramName] = [];
                }
            } elseif ($param->isCallable()) {
                if ($param->isDefaultValueAvailable()) {
                    $parameters[$paramName] = $param->getDefaultValue();
                } else {
                    $parameters[$paramName] = function ($arg) {
                        return $arg;
                    };
                }
            } else {
                if ($param->isDefaultValueAvailable()) {
                    $parameters[$paramName] = $param->getDefaultValue();
                } else {
                    if ($param->allowsNull()) {
                        $parameters[$paramName] = null;
                    } else {
                        $parameters[$paramName] = false;
                    }
                }
            }
        }
        return $parameters;
    }


    /**设置容器对象定义的配置
     * @param string $id
     * @param string | array | callable $concrete
     * @throws ContainerException
     */
    public function injection($id, $concrete)
    {
        if (is_array($concrete) && !isset($concrete['class'])) {
            throw new Exception('数组必须包含类定义');
        }
        $this->definitions[$id] = $concrete;
    }

    public function getResolvedEntries()
    {
        return $this->resolvedEntries;
    }

    public function getParams()
    {
        return $this->_params;
    }

}