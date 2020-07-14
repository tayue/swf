<?php

namespace Framework\Traits;
use Framework\SwServer\Pool\DiPool;

/**
 * Class Prototype
 *
 * @since 2.0
 */
trait PrototypeTrait
{
    /**
     * Get instance from container
     *
     * @return static
     */
    protected static function __instance()
    {
        return DiPool::getinstance()->getSingleton(static::class);
    }
}
