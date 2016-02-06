<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductImage\ProductImageFileLocator;
use LizardsAndPumpkins\Projection\Catalog\PageTitle\TwentyOneRunProductPageTitle;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\TwentyOneRunProductViewLocator
 * @uses   \LizardsAndPumpkins\Projection\Catalog\TwentyOneRunConfigurableProductView
 * @uses   \LizardsAndPumpkins\Projection\Catalog\TwentyOneRunSimpleProductView
 */
class TwentyOneRunProductViewLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TwentyOneRunProductViewLocator
     */
    private $locator;

    /**
     * @var ProductImageFileLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductImageLocator;

    protected function setUp()
    {
        /** @var TwentyOneRunProductPageTitle|\PHPUnit_Framework_MockObject_MockObject $stubPageTitle */
        $stubPageTitle = $this->getMock(TwentyOneRunProductPageTitle::class, [], [], '', false);
        $this->stubProductImageLocator = $this->getMock(ProductImageFileLocator::class);

        $this->locator = new TwentyOneRunProductViewLocator($this->stubProductImageLocator, $stubPageTitle);
    }

    public function testProductViewInterfaceIsImplemented()
    {
        $this->assertInstanceOf(ProductViewLocator::class, $this->locator);
    }

    public function testSimpleProductViewIsReturned()
    {
        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->getMock(Product::class);

        $result = $this->locator->createForProduct($stubProduct);

        $this->assertInstanceOf(TwentyOneRunSimpleProductView::class, $result);
    }

    public function testConfigurableProductViewIsReturned()
    {
        /** @var ConfigurableProduct|\PHPUnit_Framework_MockObject_MockObject $stubConfigurableProduct */
        $stubConfigurableProduct = $this->getMock(ConfigurableProduct::class, [], [], '', false);

        $result = $this->locator->createForProduct($stubConfigurableProduct);

        $this->assertInstanceOf(TwentyOneRunConfigurableProductView::class, $result);
    }
}
