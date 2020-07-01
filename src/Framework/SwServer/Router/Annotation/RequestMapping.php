<?php

namespace Framework\SwServer\Router\Annotation;


/**
 * @Annotation
 * @Target({"METHOD"})
 */
class RequestMapping extends Mapping
{
    public const GET = 'GET';

    public const POST = 'POST';

    public const PUT = 'PUT';

    public const PATCH = 'PATCH';

    public const DELETE = 'DELETE';

    public const HEADER = 'HEADER';

    public const OPTIONS = 'OPTIONS';

    /**
     * @var array
     */
    public $methods = ['GET', 'POST'];

    public function __construct($value = null)
    {
        parent::__construct($value);
        if (isset($value['methods'])) {
            if (is_string($value['methods'])) {
                // Explode a string to a array
                $this->methods = explode(',', self::upper(str_replace(' ', '', $value['methods'])));
            } else {
                $methods = [];
                foreach ($value['methods'] as $method) {
                    $methods[] = self::upper(str_replace(' ', '', $method));
                }
                $this->methods = $methods;
            }
        }
    }

    /**
     * Convert the given string to upper-case.
     */
    public static function upper(string $value): string
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    public static function getName(){
        return self::class;
    }
}
