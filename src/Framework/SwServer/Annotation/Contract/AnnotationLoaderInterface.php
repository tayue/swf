<?php


namespace Framework\SwServer\Annotation\Contract;

/**
 * Class LoaderInterface
 *
 */
interface AnnotationLoaderInterface
{


    /**
     * Get namespace and dir
     *
     * @return array
     * [
     *     namespace => dir path
     * ]
     */
    public function getPrefixDirs(): array;
}
