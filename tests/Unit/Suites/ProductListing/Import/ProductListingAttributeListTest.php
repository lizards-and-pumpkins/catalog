<?php

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\Import\Product\Listing\Exception\InvalidProductListingAttributeCodeException;
use LizardsAndPumpkins\Import\Product\Listing\Exception\InvalidProductListingAttributeValueException;
use LizardsAndPumpkins\ProductListing\Import\Exception\ProductListingAttributeNotFoundException;
use LizardsAndPumpkins\ProductListing\Import\ProductListingAttributeList;

/**
 * @covers \LizardsAndPumpkins\ProductListing\Import\ProductListingAttributeList
 */
class ProductListingAttributeListTest extends \PHPUnit_Framework_TestCase
{
    public function testExceptionIsThrownIfAttributeCodeIsNotAString()
    {
        $this->expectException(InvalidProductListingAttributeCodeException::class);
        $this->expectExceptionMessage('Product listing attribute code must be a string, got "integer".');

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
}
