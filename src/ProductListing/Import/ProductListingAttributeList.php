<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\Import\Product\Listing\Exception\InvalidProductListingAttributeCodeException;
use LizardsAndPumpkins\Import\Product\Listing\Exception\InvalidProductListingAttributeValueException;

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
    public static function fromArray(array $attributes) : ProductListingAttributeList
    {
        every($attributes, function ($value, $code) {
            self::validateAttributeCode($code);
            self::validateAttributeValue($value);
        });

        return new self($attributes);
    }

    private static function validateAttributeCode(string $code): void
    {
        if ('' === $code) {
            throw new InvalidProductListingAttributeCodeException(
                'Product listing attribute code can not be empty string.'
            );
        }
    }

    /**
     * @param int|float|string|bool $value
     */
    private static function validateAttributeValue($value): void
    {
        if (!is_scalar($value)) {
            throw new InvalidProductListingAttributeValueException(
                sprintf('The product listing attribute value must have a scalar value, got "%s"', gettype($value))
            );
        }
    }

    /**
     * @return bool[]|float[]|int[]|string[]
     */
    public function toArray(): array
    {
        return $this->attributes;
    }
}
