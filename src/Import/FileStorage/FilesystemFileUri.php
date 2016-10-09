<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\FileStorage;

use LizardsAndPumpkins\Import\FileStorage\Exception\InvalidFileURIException;

class FilesystemFileUri implements StorageSpecificFileUri
{
    /**
     * @var string
     */
    private $filesystemFilePath;

    /**
     * @param string $filesystemFilePath
     */
    private function __construct($filesystemFilePath)
    {
        $this->filesystemFilePath = $filesystemFilePath;
    }

    /**
     * @param string|FilesystemFileUri $filesystemPath
     * @return FilesystemFileUri
     */
    public static function fromString($filesystemPath) : FilesystemFileUri
    {
        if ($filesystemPath instanceof self) {
            return $filesystemPath;
        }
        if (! is_string($filesystemPath)) {
            $message = sprintf('The file URI has to be a string, got "%s"', self::getVariableType($filesystemPath));
            throw new InvalidFileURIException($message);
        }
        $trimmedFileURI = trim($filesystemPath);
        if ('' === $trimmedFileURI) {
            throw new InvalidFileURIException('The file URI must not be an empty string');
        }
        return new self($filesystemPath);
    }

    /**
     * @param mixed $variable
     * @return string
     */
    private static function getVariableType($variable) : string
    {
        return is_object($variable) ?
            get_class($variable) :
            gettype($variable);
    }

    public function __toString() : string
    {
        return $this->filesystemFilePath;
    }
}
