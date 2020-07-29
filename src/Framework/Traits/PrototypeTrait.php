<?php

namespace Framework\Traits;
use Framework\SwServer\Pool\DiPool;

trait PrototypeTrait
{
    /**
     * Get instance from container
     */
    protected static function __instance()
    {
        return DiPool::getinstance()->getSingleton(static::class);
    }
}
