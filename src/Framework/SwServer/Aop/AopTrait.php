<?php


namespace Framework\SwServer\Aop;


use Framework\SwServer\Annotation\AnnotationRegister;


trait AopTrait
{
    /**
     * AOP proxy call method
     *
     * @param \Closure $closure
     * @param string $method
     * @param array $params
     * @return mixed|null
     * @throws \Throwable
     */
    public function __proxyCall(\Closure $closure, string $method, array $params, string $className)
    {
        $proceedingJoinPoint = new ProceedingJoinPoint($closure, $className, $method, $params);
        $result = self::handleAround($proceedingJoinPoint);
        unset($proceedingJoinPoint);
        return $result;
    }

    private static function handleAround(ProceedingJoinPoint $proceedingJoinPoint)
    {
        $aspects = AnnotationRegister::getAspectObjs($proceedingJoinPoint->className, $proceedingJoinPoint->methodName);
        //$aspects = array_unique(array_merge($aspects, $annotationAspects));
        if (empty($aspects)) {
            return $proceedingJoinPoint->processOriginalMethod();
        }

//        $container = ApplicationContext::getContainer();
//        if (method_exists($container, 'make')) {
//            $pipeline = $container->make(Pipeline::class);
//        } else {
        $pipeline = new PipelineAop();
        // }
        return $pipeline->via('process')
            ->through($aspects)
            ->send($proceedingJoinPoint)
            ->then(function (ProceedingJoinPoint $proceedingJoinPoint) {
                return $proceedingJoinPoint->processOriginalMethod();
            });
    }
}
