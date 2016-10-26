<?php

declare(strict_types=1);

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
     * @param string|StorageAgnosticFileUri $fileIdentifier
     * @return StorageAgnosticFileUri
     */
    public static function fromString($fileIdentifier) : StorageAgnosticFileUri
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
    private static function getVariableType($variable) : string
    {
        return is_object($variable) ?
            get_class($variable) :
            gettype($variable);
    }

    public function __toString() : string
    {
        return $this->fileURI;
    }
}
