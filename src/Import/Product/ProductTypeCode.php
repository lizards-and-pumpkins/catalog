<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Import\Product\Exception\InvalidProductTypeCodeException;

class ProductTypeCode
{
    /**
     * @var string
     */
    private $productTypeCodeString;

    private function __construct(string $productTypeString)
    {
        $this->productTypeCodeString = $productTypeString;
    }

    public static function fromString(string $productTypeString) : ProductTypeCode
    {
        self::validateProductTypeString($productTypeString);
        return new self($productTypeString);
    }

    private static function validateProductTypeString(string $productTypeString)
    {
        if (trim($productTypeString) === '') {
            throw new InvalidProductTypeCodeException('The product type code can not be empty');
        }
    }

    public function __toString() : string
    {
        return $this->productTypeCodeString;
    }

    public function isEqualTo(ProductTypeCode $otherProductTypeCode) : bool
    {
        return $this->productTypeCodeString === $otherProductTypeCode->productTypeCodeString;
    }
}
