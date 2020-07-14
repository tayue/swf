<?php


namespace Framework\SwServer\Rpc;

use ReflectionException;

use Framework\Traits\PrototypeTrait;



class Response
{
    use PrototypeTrait;

    /**
     * @var mixed
     */
    private $result;

    /**
     * @var Error|null
     */
    private $error;

    /**
     * @param $result
     * @param $error
     *
     * @return Response
     */
    public static function new($result, $error): self
    {
        $instance = self::__instance();

        $instance->result = $result;
        $instance->error  = $error;

        return $instance;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return Error
     */
    public function getError(): ?Error
    {
        return $this->error;
    }
}