<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Product\Exception\InvalidProductListingAttributeCodeException;
use LizardsAndPumpkins\Product\Exception\InvalidProductListingAttributeValueException;
use LizardsAndPumpkins\Product\Exception\ProductListingAttributeNotFoundException;

class ProductListingAttributeList
{
    /**
     * @var bool[]|float[]|int[]|string[]
     */
    private $attributes;

    /**
     * @param int[]|float[]|string[]|bool[] $attributes
     */
    private function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @param int[]|float[]|string[]|bool[] $attributes
     * @return ProductListingAttributeList
     */
    public static function fromArray(array $attributes)
    {
        array_map([self::class, 'validateAttributeCodes'], array_keys($attributes));
        array_map([self::class, 'validateAttributeValues'], $attributes);

        return new self($attributes);
    }

    /**
     * @param string $code
     * @return bool
     */
    public function hasAttribute($code)
    {
        return isset($this->attributes[$code]);
    }

    /**
     * @param string $code
     * @return int|float|string|bool
     */
    public function getAttributeValueByCode($code)
    {
        if (!$this->hasAttribute($code)) {
            throw new ProductListingAttributeNotFoundException(
                sprintf('Product list attribute with code "%s" is not found.', $code)
            );
        }

        return $this->attributes[$code];
    }

    /**
     * @param string $code
     */
    private static function validateAttributeCodes($code)
    {
        if (!is_string($code)) {
            throw new InvalidProductListingAttributeCodeException(
                sprintf('Product listing attribute code must be a string, got "%s".', gettype($code))
            );
        }

        if ('' === $code) {
            throw new InvalidProductListingAttributeCodeException(
                'Product listing attribute code can not be empty string.'
            );
        }
    }

    /**
     * @param int|float|string|bool $value
     */
    private static function validateAttributeValues($value)
    {
        if (!is_scalar($value)) {
            throw new InvalidProductListingAttributeValueException(
                sprintf('The product listing attribute value must have a scalar value, got "%s"', gettype($value))
            );
        }
    }
}
