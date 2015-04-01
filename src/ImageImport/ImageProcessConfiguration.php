<?php

namespace Brera\ImageImport;

class ImageProcessConfiguration implements \IteratorAggregate
{
    /**
     * @var ImageProcessCommand[]
     */
    private $configurations;
    /**
     * @var string
     */
    private $targetDirectory;

    /**
     * @param ImageProcessCommand[] $configurations
     * @param string $targetDirectory
     */
    public function __construct(array $configurations, $targetDirectory)
    {
        $this->configurations = $configurations;
        $this->targetDirectory = $targetDirectory;
    }

    /**
     * @return \ArrayIterator|ImageProcessCommand[]
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->configurations);
    }

    /**
     * @return string
     */
    public function getTargetDirectory()
    {
        return $this->targetDirectory;
    }
}
