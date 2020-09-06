<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\Import\Product\Listing\Exception\InvalidProductListingAttributeCodeException;
use LizardsAndPumpkins\Import\Product\Listing\Exception\InvalidProductListingAttributeValueException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductListing\Import\ProductListingAttributeList
 */
class ProductListingAttributeListTest extends TestCase
{
    public function testExceptionIsThrownIfAttributeCodeIsNotAString(): void
    {
        $this->expectException(\TypeError::class);

        $attributeCode = 0;
        $attributeValue = 'foo';

        ProductListingAttributeList::fromArray([$attributeCode => $attributeValue]);
    }

    public function testExceptionIsThrownIfAttributeCodeIsAnEmptyString(): void
    {
        $this->expectException(InvalidProductListingAttributeCodeException::class);
        $this->expectExceptionMessage('Product listing attribute code can not be empty string.');

        $attributeCode = '';
        $attributeValue = 'foo';

        ProductListingAttributeList::fromArray([$attributeCode => $attributeValue]);
    }

    public function testExceptionIsThrownIfAttributeValueIsNotScalar(): void
    {
        $this->expectException(InvalidProductListingAttributeValueException::class);

        $attributeCode = 'foo';
        $nonScalarAttributeValue = [];

        ProductListingAttributeList::fromArray([$attributeCode => $nonScalarAttributeValue]);
    }

    public function testReturnsAttributesArray(): void
    {
        $attributesArray = [
            'foo' => 'bar',
            'baz' => 18,
            'qux' => false,
        ];

        $productListingAttributeList = ProductListingAttributeList::fromArray($attributesArray);

        $this->assertSame($attributesArray, $productListingAttributeList->toArray());
    }
}
