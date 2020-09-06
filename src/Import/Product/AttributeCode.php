<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Import\Product\Exception\InvalidAttributeCodeException;

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
     * @param string|AttributeCode $attributeCode
     * @return AttributeCode
     */
    public static function fromString($attributeCode): AttributeCode
    {
        if ($attributeCode instanceof AttributeCode) {
            return $attributeCode;
        }
        self::validateAttributeCode($attributeCode);
        return new self($attributeCode);
    }

    private static function validateAttributeCode(string $attributeCode): void
    {
        if (strlen($attributeCode) < 3) {
            $message = sprintf('The attribute code has to be at least 3 characters long, got "%s"', $attributeCode);
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
            $message = sprintf('The attribute code may not end with an underscore, got "%s"', $attributeCode);
            throw new InvalidAttributeCodeException($message);
        }
    }

    public function __toString() : string
    {
        return $this->code;
    }

    public function jsonSerialize() : string
    {
        return $this->code;
    }

    /**
     * @param string|AttributeCode $attributeCode
     * @return bool
     */
    public function isEqualTo($attributeCode) : bool
    {
        return $this->code === (string) $attributeCode;
    }
}
