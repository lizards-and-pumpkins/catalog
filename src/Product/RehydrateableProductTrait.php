<?php


namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Product\Exception\ProductTypeCodeMismatchException;
use LizardsAndPumpkins\Product\Exception\ProductTypeCodeMissingException;

trait RehydrateableProductTrait
{
    /**
     * @param string $expectedType
     * @param mixed[] $sourceArray
     */
    protected static function validateTypeCodeInSourceArray($expectedType, array $sourceArray)
    {
        if (! isset($sourceArray[Product::TYPE_KEY])) {
            $message = sprintf('The array key "%s" is missing from source array', Product::TYPE_KEY);
            throw new ProductTypeCodeMissingException($message);
        }
        if ($expectedType !== $sourceArray[Product::TYPE_KEY]) {
            $variableType = self::getVariableAsString($sourceArray[Product::TYPE_KEY]);
            $message = sprintf('Expected the product type code string "%s", got "%s"', $expectedType, $variableType);
            throw new ProductTypeCodeMismatchException($message);
        }
    }

    /**
     * @param mixed $variable
     * @return string
     */
    private static function getVariableAsString($variable)
    {
        if (is_string($variable)) {
            return $variable;
        }
        return is_object($variable) ?
            get_class($variable) :
            gettype($variable);
    }
}
