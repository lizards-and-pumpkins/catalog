<?php

namespace Brera\ImageImport;

class ImageProcessConfiguration implements \IteratorAggregate
{
    /**
     * @var ImageProcessCommandSequence[]
     */
    private $configurations;
    /**
     * @var string
     */
    private $targetDirectory;

    /**
     * @param ImageProcessCommandSequence[] $configurations
     * @param string $targetDirectory
     */
    public function __construct(array $configurations, $targetDirectory)
    {
        if (!is_dir($targetDirectory) || !is_writeable($targetDirectory)) {
            throw new InvalidConfigurationException('Target directory is no directory or not writable!');
        }
        $this->configurations = $configurations;
        $this->targetDirectory = $targetDirectory;
    }

    /**
     * @return \ArrayIterator|ImageProcessCommandSequence[]
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
