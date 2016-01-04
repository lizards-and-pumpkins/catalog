<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Product\Exception\InvalidProductTypeCodeException;

class ProductTypeCode
{
    /**
     * @var string
     */
    private $productTypeCodeString;

    /**
     * @param string $productTypeString
     */
    private function __construct($productTypeString)
    {
        $this->productTypeCodeString = $productTypeString;
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
            throw self::getInvalidProductTypeCodeException($productTypeString);
        }
        if (trim($productTypeString) === '') {
            throw self::getEmptyProductTypeCodeException();
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
     * @return InvalidProductTypeCodeException
     */
    private static function getInvalidProductTypeCodeException($productTypeId)
    {
        $variableType = self::getVariableType($productTypeId);
        $message = sprintf('The product type code has to be a string, got "%s"', $variableType);
        return new InvalidProductTypeCodeException($message);
    }

    /**
     * @return InvalidProductTypeCodeException
     */
    private static function getEmptyProductTypeCodeException()
    {
        return new InvalidProductTypeCodeException('The product type code can not be empty');
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->productTypeCodeString;
    }

    /**
     * @param ProductTypeCode $otherProductTypeCode
     * @return bool
     */
    public function isEqualTo(ProductTypeCode $otherProductTypeCode)
    {
        return $this->productTypeCodeString === $otherProductTypeCode->productTypeCodeString;
    }
}
