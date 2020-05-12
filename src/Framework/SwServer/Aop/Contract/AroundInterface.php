<?php


namespace Framework\SwServer\Aop\Contract;

use Framework\SwServer\Aop\ProceedingJoinPoint;

interface AroundInterface
{
    /**
     * @return mixed return the value from process method of ProceedingJoinPoint, or the value that you handled
     */
    public function process(ProceedingJoinPoint $proceedingJoinPoint);
}