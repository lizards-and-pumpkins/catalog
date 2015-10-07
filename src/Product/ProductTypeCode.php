<?php


namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Product\Exception\InvalidProductTypeIdentifierException;

class ProductTypeCode
{
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
     * @return string
     */
    public function __toString()
    {
        return $this->productTypeString;
    }
}
