<?php


namespace Framework\SwServer\Rpc\Annotation;


use Doctrine\Common\Annotations\Annotation\Attribute;
use Doctrine\Common\Annotations\Annotation\Attributes;
use Doctrine\Common\Annotations\Annotation\Target;
use Framework\SwServer\Rpc\Protocol;
use Framework\SwServer\Rpc\Contract\RpcServiceRouterInterface;

/**
 * Class Service
 *
 * @since 2.0
 *
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes({
 *     @Attribute("version", type="string"),
 * })
 */
class RpcServiceRouter implements RpcServiceRouterInterface
{
    /**
     * @var string
     */
    private $version = Protocol::DEFAULT_VERSION;

    /**
     * Service constructor.
     *
     * @param array $values
     */
    public function __construct(array $values)
    {
        if (isset($values['value'])) {
            $this->version = $values['value'];
        }
        if (isset($values['version'])) {
            $this->version = $values['version'];
        }
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }
}