<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\VersionedContext;
use LizardsAndPumpkins\Product\Exception\InvalidProductListingSourceDataException;

/**
 * @covers \LizardsAndPumpkins\Product\ProductsPerPageForContextList
 * @uses   \LizardsAndPumpkins\Product\ProductsPerPageForContext
 */
class ProductsPerPageForContextListTest extends \PHPUnit_Framework_TestCase
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
        $productsPerPageForContextList = ProductsPerPageForContextList::fromArray($sourceDataPairs);

        $result = $productsPerPageForContextList->getListOfAvailableNumberOfProductsPerPageForContext($stubContextA);

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

        ProductsPerPageForContextList::fromArray($sourceDataPairs);
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

        ProductsPerPageForContextList::fromArray($sourceDataPairs);
    }
}
