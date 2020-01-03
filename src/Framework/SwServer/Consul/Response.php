<?php declare(strict_types=1);


namespace Framework\SwServer\Consul;


use Framework\Traits\SingletonTrait;
use Framework\Tool\Tool;

/**
 * Class Response
 *
 * @since 2.0
 *
 * @Bean(scope=Bean::PROTOTYPE)
 */
class Response
{
    use SingletonTrait;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var string
     */
    private $body;

    /**
     * @var int
     */
    private $status;

    /**
     * @param array $headers
     * @param string $body
     * @param int $status
     *
     * @return Response
     */
    public static function new(array $headers, string $body, int $status = 200): self
    {
        $self = self::getInstance();

        $self->body = $body;
        $self->status = $status;
        $self->headers = $headers;

        return $self;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->status;
    }

    /**
     * @return array|mixed
     */
    public function getResult()
    {
        if (empty($this->body)) {
            return $this->body;
        }

        return Tool::decode($this->body, true);
    }
}