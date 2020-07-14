<?php


namespace Framework\SwServer\Rpc\Router;


use function sprintf;

use Framework\SwServer\Rpc\Contract\RouterInterface;


class Router implements RouterInterface
{
    /**
     * @var array
     *
     * @example
     * [
     *    'interface@version' => $className
     * ]
     */
    private $routes = [];

    /**
     * @param string $interface
     * @param string $version
     * @param string $className
     */
    public function addRoute(string $interface, string $version, string $className): void
    {
        $route = $this->getRoute($interface, $version);

        $this->routes[$route] = $className;
    }

    /**
     * @param string $version
     * @param string $interface
     *
     * @return array
     */
    public function match(string $version, string $interface): array
    {
        $route = $this->getRoute($interface, $version);

        if (isset($this->routes[$route])) {
            return [self::FOUND, $this->routes[$route]];
        }

        return [self::NOT_FOUND, ''];
    }

    /**
     * @param string $interface
     * @param string $version
     *
     * @return string
     */
    private function getRoute(string $interface, string $version): string
    {
        return sprintf('%s@%s', $interface, $version);
    }

    /**
     * @return array
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
