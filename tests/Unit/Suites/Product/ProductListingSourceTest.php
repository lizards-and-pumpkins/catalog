<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;

/**
 * @covers \LizardsAndPumpkins\Product\ProductListingSource
 */
class ProductListingSourceTest extends \PHPUnit_Framework_TestCase
{
    public function testProductListingSourceContextAndNumberOfItemsPerPageAreReturned()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);
        $productListingSource = new ProductListingSource($stubContext, 1);

        $context = $productListingSource->getContext();
        $numItemsPerPage = $productListingSource->getNumItemsPerPage();

        $this->assertSame($stubContext, $context);
        $this->assertEquals(1, $numItemsPerPage);
    }
}
