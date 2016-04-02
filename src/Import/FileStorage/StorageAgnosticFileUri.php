<?php

namespace LizardsAndPumpkins\Import\FileStorage;

use LizardsAndPumpkins\Import\FileStorage\Exception\InvalidFileIdentifierException;

class StorageAgnosticFileUri
{
    /**
     * @var string
     */
    private $fileURI;

    /**
     * @param string $fileURI
     */
    private function __construct($fileURI)
    {
        $this->fileURI = $fileURI;
    }
    
    /**
     * @param string $fileIdentifier
     * @return StorageAgnosticFileUri
     */
    public static function fromString($fileIdentifier)
    {
        if ($fileIdentifier instanceof self) {
            return $fileIdentifier;
        }
        if (! is_string($fileIdentifier)) {
            $type = self::getVariableType($fileIdentifier);
            throw new InvalidFileIdentifierException(
                sprintf('The file identifier has to be a string, got "%s"', $type)
            );
        }
        $trimmedIdentifier = trim($fileIdentifier);
        if ('' === $trimmedIdentifier) {
            throw new InvalidFileIdentifierException('The file identifier must not be empty');
        }
        return new self($trimmedIdentifier);
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
        return $this->fileURI;
    }
}
