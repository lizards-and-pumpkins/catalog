<?php

namespace LizardsAndPumpkins\ProductRelations\ContentDelivery;

use LizardsAndPumpkins\ProductRelations\Exception\InvalidProductRelationTypeCodeException;

class ProductRelationTypeCode
{
    /**
     * @var string
     */
    private $productRelationTypeCode;

    /**
     * @param string $productRelationTypeCode
     */
    private function __construct($productRelationTypeCode)
    {
        $this->productRelationTypeCode = $productRelationTypeCode;
    }

    /**
     * @param string $relationTypeCode
     * @return ProductRelationTypeCode
     */
    public static function fromString($relationTypeCode)
    {
        self::validateType($relationTypeCode);
        $trimmedRelationTypeCode = trim($relationTypeCode);
        self::validateStringFormat($trimmedRelationTypeCode);
        return new self($trimmedRelationTypeCode);
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
     * @param mixed $relationTypeCode
     */
    private static function validateType($relationTypeCode)
    {
        if (!is_string($relationTypeCode)) {
            $type = self::getVariableType($relationTypeCode);
            $message = sprintf('Expected the product relation type code to be a string, got "%s"', $type);
            throw new InvalidProductRelationTypeCodeException($message);
        }
    }

    /**
     * @param string $relationTypeCode
     */
    private static function validateStringFormat($relationTypeCode)
    {
        if ('' === $relationTypeCode) {
            throw new InvalidProductRelationTypeCodeException('The product relation type code can not be empty');
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->productRelationTypeCode;
    }
}
