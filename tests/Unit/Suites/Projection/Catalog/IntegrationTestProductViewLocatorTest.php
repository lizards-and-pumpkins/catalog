<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductImage\ProductImageFileLocator;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\IntegrationTestProductViewLocator
 * @uses   \LizardsAndPumpkins\Projection\Catalog\IntegrationTestProductView
 */
class IntegrationTestProductViewLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var IntegrationTestProductViewLocator
     */
    private $locator;

    protected function setUp()
    {
        $stubProductImageFileLocator = $this->getMock(ProductImageFileLocator::class);
        $this->locator = new IntegrationTestProductViewLocator($stubProductImageFileLocator);
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
