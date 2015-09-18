<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;

/**
 * @covers \LizardsAndPumpkins\Product\ProductsPerPageForContext
 */
class ProductsPerPageForContextTest extends \PHPUnit_Framework_TestCase
{
    public function testProductListingContextAndNumberOfItemsPerPageAreReturned()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);
        $productsPerPageForContext = new ProductsPerPageForContext($stubContext, 1);

        $context = $productsPerPageForContext->getContext();
        $numItemsPerPage = $productsPerPageForContext->getNumItemsPerPage();

        $this->assertSame($stubContext, $context);
        $this->assertEquals(1, $numItemsPerPage);
    }
}
