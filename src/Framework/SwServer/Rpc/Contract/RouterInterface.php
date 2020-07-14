<?php


namespace Framework\SwServer\Rpc\Contract;

/**
 * Class RouterInterface
 *
 *
 */
interface RouterInterface
{

    /**
     * Found route
     */
    public const FOUND     = 1;

    /**
     * Not found
     */
    public const NOT_FOUND = 2;

    /**
     * @param string $interface
     * @param string $version
     * @param string $className
     */
    public function addRoute(string $interface, string $version, string $className): void;

    /**
     * @param string $version
     * @param string $interface
     *
     * @return array
     */
    public function match(string $version, string $interface): array;
}