<?php

namespace Framework\SwServer\Router;

use FastRoute\DataGenerator\GroupCountBased as DataGenerator;
use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased;
use FastRoute\RouteParser\Std;
use Framework\SwServer\Annotation\AnnotationRegister;
use Framework\SwServer\Router\Annotation\Controller;
use Exception;
use ReflectionMethod;

class DispatcherFactory
{
    protected $routes = [ROOT_PATH . '/Config/routes.php'];

    /**
     * @var \FastRoute\RouteCollector[]
     */
    protected $routers = [];

    /**
     * @var Dispatcher[]
     */
    protected $dispatchers = [];

    public function __construct()
    {
        $this->initAnnotationRoute(AnnotationRegister::getRouteAnnotations());
        $this->initConfigRoute();
    }

    public function getDispatcher(string $serverName='http'): Dispatcher
    {
        if (isset($this->dispatchers[$serverName])) {
            return $this->dispatchers[$serverName];
        }
        $router = $this->getRouter($serverName);
        return $this->dispatchers[$serverName] = new GroupCountBased($router->getData());
    }

    public function initConfigRoute()
    {
        Router::init($this);
        foreach ($this->routes as $route) {
            if (file_exists($route)) {
                require_once $route;
            }
        }
    }

    public function getRouter(string $serverName): RouteCollector
    {
        if (isset($this->routers[$serverName])) {
            return $this->routers[$serverName];
        }
        $parser = new Std();
        $generator = new DataGenerator();
        return $this->routers[$serverName] = new RouteCollector($parser, $generator, $serverName);
    }

    protected function initAnnotationRoute(array $collector): void
    {
        foreach ($collector as $className => $metadata) {
            if (isset($metadata['class'])) {
                $this->parseController($className, $metadata['class'], $metadata['methods'] ?? [], []);
            }
        }
    }

    protected function parseController(string $className, Controller $annotation, array $methodMetadata, array $middlewares = []): void
    {
        if (!$methodMetadata) {
            return;
        }
        $prefix = $annotation->prefix;
        if (!$prefix) {
            throw new Exception("Router Prefix Not Exists !!");
        }
        if ($prefix != '/') {
            $prefix = '/' . $prefix;
        }
        $router = $this->getRouter($annotation->server);
        foreach ($methodMetadata as $methodName => $methodAnnotation) {
            /** @var Mapping $mapping */
            if ($mapping = $methodAnnotation ?? null) {
                if (!isset($mapping->path) || !isset($mapping->methods)) {
                    continue;
                }
                $path = $mapping->path;

                if ($path === '') {
                    $path = $prefix;
                } elseif ($path !== '/') {
                    $path = $prefix . '/' . $path;
                }
                $router->addRoute($mapping->methods, $path, [$className, $methodName], []);
            }

        }
    }


}
