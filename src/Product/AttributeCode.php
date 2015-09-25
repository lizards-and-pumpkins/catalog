<?php


namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Product\Exception\InvalidAttributeCodeException;

class AttributeCode implements \JsonSerializable
{
    /**
     * @var string
     */
    private $code;

    /**
     * @param string $attributeCode
     */
    private function __construct($attributeCode)
    {
        $this->code = $attributeCode;
    }

    /**
     * @param string $attributeCode
     * @return AttributeCode
     */
    public static function fromString($attributeCode)
    {
        self::validateAttributeCode($attributeCode);
        return new self($attributeCode);
    }

    /**
     * @param mixed $variable
     * @return string
     */
    private static function getType($variable)
    {
        return is_object($variable) ?
            get_class($variable) :
            gettype($variable);
    }

    /**
     * @param mixed $attributeCode
     */
    private static function validateAttributeCode($attributeCode)
    {
        if (!is_string($attributeCode)) {
            $message = sprintf('The attribute code has to be a string, got "%s"', self::getType($attributeCode));
            throw new InvalidAttributeCodeException($message);
        }
        if (empty($attributeCode)) {
            $message = sprintf('The attribute code has to be at least 3 characters long, got ""', $attributeCode);
            throw new InvalidAttributeCodeException($message);
        }
        $chr = ord(substr($attributeCode, 0, 1));
        if ($chr < ord('a') || $chr > ord('z')) {
            $message = sprintf('The first letter of the attribute code has to be a character from a-z, got "%c"', $chr);
            throw new InvalidAttributeCodeException($message);
        }
        if (preg_match('/[^a-z0-9_]/', $attributeCode)) {
            $message = sprintf(
                'The attribute code may only contain letters from a-z, numbers and underscores, got "%s"',
                $attributeCode
            );
            throw new InvalidAttributeCodeException($message);
        }
        if (substr($attributeCode, -1) === '_') {
            $message = sprintf('The attribute code may not and with an underscore, got "%s"', $attributeCode);
            throw new InvalidAttributeCodeException($message);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function jsonSerialize()
    {
        return $this->code;
    }
}
