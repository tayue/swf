<?php

namespace Framework\SwServer\Router\Annotation;


abstract class Mapping
{
    public $methods;

    public $path;

    public function __construct($value = null)
    {
        if (isset($value['path'])) {
            $this->path = $value['path'];
        }
        $this->bindMainProperty('path', $value);
    }

    protected function bindMainProperty(string $key, ?array $value)
    {
        if (isset($value['value'])) {
            $this->{$key} = $value['value'];
        }
    }
}
