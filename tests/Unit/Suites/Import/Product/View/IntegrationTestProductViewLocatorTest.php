<?php

namespace LizardsAndPumpkins\Import\Product\View;

use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\View\CompositeProductView;
use LizardsAndPumpkins\Import\Product\View\IntegrationTestProductViewLocator;
use LizardsAndPumpkins\Import\Product\View\ProductImageFileLocator;
use LizardsAndPumpkins\Import\Product\View\ProductView;
use LizardsAndPumpkins\Import\Product\View\ProductViewLocator;

/**
 * @covers \LizardsAndPumpkins\Import\Product\View\IntegrationTestProductViewLocator
 * @uses   \LizardsAndPumpkins\Import\Product\View\IntegrationTestProductView
 * @uses   \LizardsAndPumpkins\Import\Product\View\IntegrationTestConfigurableProductView
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

    public function testItReturnsAConfigurableProductViewForConfigurableProducts()
    {
        /** @var ConfigurableProduct|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->getMock(ConfigurableProduct::class, [], [], '', false);

        $result = $this->locator->createForProduct($stubProduct);

        $this->assertInstanceOf(CompositeProductView::class, $result);
    }
}
