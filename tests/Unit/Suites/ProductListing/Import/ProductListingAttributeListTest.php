<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\Import\Product\Listing\Exception\InvalidProductListingAttributeCodeException;
use LizardsAndPumpkins\Import\Product\Listing\Exception\InvalidProductListingAttributeValueException;
use LizardsAndPumpkins\ProductListing\Import\Exception\ProductListingAttributeNotFoundException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductListing\Import\ProductListingAttributeList
 */
class ProductListingAttributeListTest extends TestCase
{
    public function testExceptionIsThrownIfAttributeCodeIsNotAString()
    {
        $this->expectException(\TypeError::class);

        $attributeCode = 0;
        $attributeValue = 'foo';

        ProductListingAttributeList::fromArray([$attributeCode => $attributeValue]);
    }

    public function testExceptionIsThrownIfAttributeCodeIsAnEmptyString()
    {
        $this->expectException(InvalidProductListingAttributeCodeException::class);
        $this->expectExceptionMessage('Product listing attribute code can not be empty string.');

        $attributeCode = '';
        $attributeValue = 'foo';

        ProductListingAttributeList::fromArray([$attributeCode => $attributeValue]);
    }

    public function testExceptionIsThrownIfAttributeValueIsNotScalar()
    {
        $this->expectException(InvalidProductListingAttributeValueException::class);

        $attributeCode = 'foo';
        $nonScalarAttributeValue = [];

        ProductListingAttributeList::fromArray([$attributeCode => $nonScalarAttributeValue]);
    }

    public function testFalseIsReturnedIfRequestedAttributeCodeIsAbsentInTheList()
    {
        $productListingAttributeList = ProductListingAttributeList::fromArray([]);
        $this->assertFalse($productListingAttributeList->hasAttribute('foo'));
    }

    public function testTrueIsReturnedIfListContainsAttributeWithARequestedCode()
    {
        $attributeCode = 'foo';
        $attributeValue = 'bar';
        $productListingAttributeList = ProductListingAttributeList::fromArray([$attributeCode => $attributeValue]);

        $this->assertTrue($productListingAttributeList->hasAttribute($attributeCode));
    }

    public function testExceptionIsThrownDuringAttemptToRetrieveAttributeWhichIsAbsentInTheList()
    {
        $this->expectException(ProductListingAttributeNotFoundException::class);
        $productListingAttributeList = ProductListingAttributeList::fromArray([]);
        $productListingAttributeList->getAttributeValueByCode('foo');
    }

    public function testAttributeIsReturnedByGivenCode()
    {
        $attributeCode = 'foo';
        $attributeValue = 'bar';
        $productListingAttributeList = ProductListingAttributeList::fromArray([$attributeCode => $attributeValue]);

        $this->assertSame($attributeValue, $productListingAttributeList->getAttributeValueByCode($attributeCode));
    }

    public function testReturnsAttributesArray()
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
