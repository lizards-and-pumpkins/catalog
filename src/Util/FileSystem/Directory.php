<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Util\FileSystem;

use LizardsAndPumpkins\Util\FileSystem\Exception\FileAlreadyExistsWithinGivenPathException;

class Directory
{
    /**
     * @var string
     */
    private $directoryPath;

    private function __construct(string $directoryPath)
    {
        $this->directoryPath = $directoryPath;
    }

    public static function fromPath(string $directoryPath) : Directory
    {
        if (is_file($directoryPath)) {
            throw new FileAlreadyExistsWithinGivenPathException(
                sprintf('The specified directory is a file: %s.', $directoryPath)
            );
        }

        return new self($directoryPath);
    }

    public function isReadable() : bool
    {
        return is_readable($this->directoryPath);
    }

    public function getPath() : string
    {
        return $this->directoryPath;
    }
}
