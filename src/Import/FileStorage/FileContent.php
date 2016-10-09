<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\FileStorage;

use LizardsAndPumpkins\Import\FileStorage\Exception\InvalidFileContentTypeException;

class FileContent
{
    /**
     * @var string|File
     */
    private $content;

    /**
     * @param string|File $content
     */
    private function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * @param mixed $content
     * @return FileContent
     */
    public static function fromString($content) : FileContent
    {
        if (! self::isCastableToString($content)) {
            throw new InvalidFileContentTypeException(
                sprintf('Unable to cast file content to string, got "%s"', self::getVariableType($content))
            );
        }
        return new self((string) $content);
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

    /**
     * @param mixed $variable
     * @return bool
     */
    private static function isCastableToString($variable): bool
    {
        if (is_array($variable)) {
            return false;
        }
        if (is_object($variable) && ! method_exists($variable, '__toString')) {
            return false;
        }
        return true;
    }

    public static function fromFile(File $file) : FileContent
    {
        return new self($file);
    }

    public function __toString() : string
    {
        return $this->content instanceof File ?
            (string) $this->content->getContent() :
            $this->content;
    }
}
