<?php

namespace LizardsAndPumpkins\Utils\FileStorage;

use LizardsAndPumpkins\Utils\FileStorage\Exception\InvalidFileContentTypeException;

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
     * @param string $content
     * @return FileContent
     */
    public static function fromString($content)
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
    private static function getVariableType($variable)
    {
        return is_object($variable) ?
            get_class($variable) :
            gettype($variable);
    }

    /**
     * @param mixed $variable
     * @return bool
     */
    private static function isCastableToString($variable)
    {
        if (is_array($variable)) {
            return false;
        }
        if (is_object($variable) && ! method_exists($variable, '__toString')) {
            return false;
        }
        return true;
    }

    /**
     * @param File $file
     * @return FileContent
     */
    public static function fromFile(File $file)
    {
        return new self($file);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->content instanceof File ?
            $this->content->getContent() :
            $this->content;
    }
}
