<?php

namespace LizardsAndPumpkins\Utils\FileStorage;

use LizardsAndPumpkins\Utils\FileStorage\Exception\InvalidFileURIException;

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
     * @param string $filesystemPath
     * @return FilesystemFileUri
     */
    public static function fromString($filesystemPath)
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
    private static function getVariableType($variable)
    {
        return is_object($variable) ?
            get_class($variable) :
            gettype($variable);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->filesystemFilePath;
    }
}
