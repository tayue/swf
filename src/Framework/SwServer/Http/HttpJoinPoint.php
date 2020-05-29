<?php


namespace Framework\SwServer\Http;

use Closure;
use Exception;

class HttpJoinPoint
{
    /**
     * @var Closure
     */
    public $originalMethod;


    /**
     * @var null|Closure
     */
    public $pipe;

    public function __construct(Closure $originalMethod)
    {
        $this->originalMethod = $originalMethod;

    }


    public function process()
    {
        $closure = $this->pipe;
        if (!$closure instanceof Closure) {
            throw new Exception('The pipe is not instanceof \Closure');
        }

        return $closure($this);
    }

    public function processOriginalMethod()
    {
        $this->pipe = null;
        $closure = $this->originalMethod;
        return $closure();
    }

}