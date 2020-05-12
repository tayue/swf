<?php


namespace Framework\SwServer\Aop;
use Framework\Tool\Pipeline;
use Closure;
use Exception;

class PipelineAop extends Pipeline
{
    protected function carry(): Closure
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                if (is_string($pipe) && class_exists($pipe)) {
                    $pipe = new $pipe();
                }
                if(!is_object($pipe)){
                    throw new Exception('$pipe must is a object.');
                }
                if (! $passable instanceof ProceedingJoinPoint) {
                    throw new InvalidDefinitionException('$passable must is a ProceedingJoinPoint object.');
                }
                $passable->pipe = $stack;
                return method_exists($pipe, $this->method) ? $pipe->{$this->method}($passable) : $pipe($passable);
            };
        };
    }
}