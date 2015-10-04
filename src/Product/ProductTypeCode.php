<?php


namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Product\Exception\InvalidProductTypeIdentifierException;

class ProductTypeCode
{
    private static $productTypeCode = [
        'simple',
        'configurable'
    ];

    /**
     * @var string
     */
    private $productTypeString;

    /**
     * @param string $productTypeString
     */
    private function __construct($productTypeString)
    {
        $this->productTypeString = $productTypeString;
    }

    /**
     * @param string $productTypeString
     * @return ProductTypeCode
     */
    public static function fromString($productTypeString)
    {
        self::validateProductTypeString($productTypeString);
        return new self($productTypeString);
    }

    /**
     * @param mixed $productTypeString
     */
    private static function validateProductTypeString($productTypeString)
    {
        if (!is_string($productTypeString)) {
            throw self::getInvalidProductTypeIdentifierTypeException($productTypeString);
        }
        if (empty(trim($productTypeString))) {
            throw self::getEmptyProductTypeIdentifierException();
        }
        if (!in_array($productTypeString, self::$productTypeCode)) {
            throw self::getInvalidProductTypeIdentifierCodeException($productTypeString);
        }
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
     * @param mixed $productTypeId
     * @return InvalidProductTypeIdentifierException
     */
    private static function getInvalidProductTypeIdentifierTypeException($productTypeId)
    {
        $variableType = self::getVariableType($productTypeId);
        $message = sprintf('The product type identifier has to be a string, got "%s"', $variableType);
        return new InvalidProductTypeIdentifierException($message);
    }

    /**
     * @return InvalidProductTypeIdentifierException
     */
    private static function getEmptyProductTypeIdentifierException()
    {
        return new InvalidProductTypeIdentifierException('The product type identifier can not be empty');
    }

    /**
     * @param string $typeCode
     * @return InvalidProductTypeIdentifierException
     */
    private static function getInvalidProductTypeIdentifierCodeException($typeCode)
    {
        $type = implode('", "', self::$productTypeCode);
        $message = sprintf('The product type identifier "%s" is invalid, expected one of "%s"', $typeCode, $type);
        return new InvalidProductTypeIdentifierException($message);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->productTypeString;
    }
}
