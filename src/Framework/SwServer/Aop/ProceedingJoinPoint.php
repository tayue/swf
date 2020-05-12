<?php


namespace Framework\SwServer\Aop;

use Closure;
use Exception;

class ProceedingJoinPoint
{
    /**
     * @var string
     */
    public $className;

    /**
     * @var string
     */
    public $methodName;

    /**
     * @var mixed[]
     */
    public $arguments;

    /**
     * @var mixed
     */
    public $result;

    /**
     * @var Closure
     */
    public $originalMethod;

    /**
     * @var null|Closure
     */
    public $pipe;

    public function __construct(Closure $originalMethod, string $className, string $methodName, array $arguments)
    {
        $this->originalMethod = $originalMethod;
        $this->className = $className;
        $this->methodName = $methodName;
        $this->arguments = $arguments;
    }

    /**
     * Delegate to the next aspect.
     */
    public function process()
    {
        $closure = $this->pipe;
        if (!$closure instanceof Closure) {
            throw new Exception('The pipe is not instanceof \Closure');
        }

        return $closure($this);
    }

    /**
     * Process the original method, this method should trigger by pipeline.
     */
    public function processOriginalMethod()
    {
        $this->pipe = null;
        $closure = $this->originalMethod;
        $arguments = $this->arguments;
//        if (count($this->arguments['keys']) > 1) {
//            $arguments = $this->getArguments();
//        } else {
//            $arguments = array_values($this->arguments['keys']);
//        }

        return $closure(...$arguments);

    }

}