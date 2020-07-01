<?php

namespace Framework\SwServer\Router;

class Handler
{
    /**
     * @var array|callable|string
     */
    public $callback;

    /**
     * @var string
     */
    public $route;

    public function __construct($callback, string $route)
    {
        $this->callback = $callback;
        $this->route = $route;
    }
}
