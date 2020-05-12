<?php

namespace Framework\SwServer\Aop;
use Framework\SwServer\Aop\Contract\AroundInterface;

abstract class AbstractAspect implements AroundInterface
{
    /**
     * The classes that you want to weaving.
     *
     * @var array
     */
    public $classes = [];

    /**
     * The annotations that you want to weaving.
     *
     * @var array
     */
    public $annotations = [];
}
