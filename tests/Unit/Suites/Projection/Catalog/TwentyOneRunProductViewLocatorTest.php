<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Product\Product;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\TwentyOneRunProductViewLocator
 * @uses   \LizardsAndPumpkins\Projection\Catalog\TwentyOneRunSimpleProductView
 */
class TwentyOneRunProductViewLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TwentyOneRunProductViewLocator
     */
    private $locator;

    protected function setUp()
    {
        $this->locator = new TwentyOneRunProductViewLocator();
    }

    public function testProductViewInterfaceIsImplemented()
    {
        $this->assertInstanceOf(ProductViewLocator::class, $this->locator);
    }

    public function testProductViewIsReturned()
    {
        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->getMock(Product::class);

        $result = $this->locator->createForProduct($stubProduct);

        $this->assertInstanceOf(ProductView::class, $result);
    }
}
