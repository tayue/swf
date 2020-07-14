<?php


namespace Framework\SwServer\Rpc;


use Framework\Traits\PrototypeTrait;



class Error
{
    use PrototypeTrait;

    /**
     * @var int
     */
    private $code = 0;

    /**
     * @var string
     */
    private $message = '';

    /**
     * @var mixed
     */
    private $data;

    /**
     * @param int    $code
     * @param string $message
     * @param mixed  $data
     *
     *
     */
    public static function new(int $code, string $message, $data)
    {
        $instance = self::__instance();

        $instance->code    = $code;
        $instance->message = $message;
        $instance->data    = $data;

        return $instance;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}