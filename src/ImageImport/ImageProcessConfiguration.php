<?php

namespace Brera\ImageImport;

class ImageProcessConfiguration implements \IteratorAggregate
{
    /**
     * @var ImageProcessCommand[]
     */
    private $configurations;

    /**
     * @param ImageProcessCommand[] $configurations
     */
    public function __construct(array $configurations)
    {
        $this->configurations = $configurations;
    }

    /**
     * @return \ArrayIterator|ImageProcessCommand[]
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->configurations);
    }
}
