<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\VersionedContext;
use Brera\Product\Exception\InvalidProductListingSourceDataException;

/**
 * @covers \Brera\Product\ProductListingSourceList
 * @uses   \Brera\Product\ProductListingSource
 */
class ProductListingSourceListTest extends \PHPUnit_Framework_TestCase
{
    public function testNumbersOfItemsPerPageForGivenContextIsReturned()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContextA */
        $stubContextA = $this->getMock(Context::class);
        $stubContextB = $this->getMock(VersionedContext::class, [], [], '', false);

        $sourceDataPairs = [
            ['context' => $stubContextA, 'numItemsPerPage' => 10],
            ['context' => $stubContextB, 'numItemsPerPage' => 20],
            ['context' => $stubContextA, 'numItemsPerPage' => 30],
        ];
        $productListingSourceList = ProductListingSourceList::fromArray($sourceDataPairs);

        $result = $productListingSourceList->getListOfAvailableNumberOfProductsPerPageForContext($stubContextA);

        $this->assertSame([10, 30], $result);
    }

    public function testExceptionIsThrownIfDataDoesNotHaveValidContext()
    {
        $stubContext = $this->getMock(Context::class);
        $sourceDataPairs = [
            ['context' => $stubContext, 'numItemsPerPage' => 1],
            ['context' => 1, 'numItemsPerPage' => 0]
        ];

        $this->setExpectedException(
            InvalidProductListingSourceDataException::class,
            'No valid context found in one or more root snippet source data pairs.'
        );

        ProductListingSourceList::fromArray($sourceDataPairs);
    }

    public function testExceptionIsThrownIfDataDoesNotHaveValidNumberOfItemsPerPage()
    {
        $stubContext = $this->getMock(Context::class);
        $sourceDataPairs = [
            ['context' => $stubContext, 'numItemsPerPage' => 'a']
        ];

        $this->setExpectedException(
            InvalidProductListingSourceDataException::class,
            'No valid number of items per page found in one or more root snippet source data pairs.'
        );

        ProductListingSourceList::fromArray($sourceDataPairs);
    }
}
