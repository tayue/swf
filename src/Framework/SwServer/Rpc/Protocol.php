<?php
/**
 * Rpc协议
 */

namespace Framework\SwServer\Rpc;

use ReflectionException;


use Framework\Traits\PrototypeTrait;

class Protocol
{
    /**
     * Default version
     */
    const DEFAULT_VERSION = '1.0';

    use PrototypeTrait;

    /**
     * @var string
     */
    private $interface = '';

    /**
     * @var string
     */
    private $method = '';

    /**
     * @var array
     */
    private $params = [];

    /**
     * @var array
     */
    private $ext = [];

    /**
     * @var string
     */
    private $version = self::DEFAULT_VERSION;

    /**
     * Replace constructor
     *
     * @param string $version
     * @param string $interface
     * @param string $method
     * @param array  $params
     * @param array  $ext
     *
     * @return Protocol
     */
    public static function new(string $version, string $interface, string $method, array $params, array $ext)
    {
        $instance = self::__instance();

        $instance->version   = $version;
        $instance->interface = $interface;
        $instance->method    = $method;
        $instance->params    = $params;
        $instance->ext       = $ext;

        return $instance;
    }

    /**
     * @return string
     */
    public function getInterface(): string
    {
        return $this->interface;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @return array
     */
    public function getExt(): array
    {
        return $this->ext;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }
}