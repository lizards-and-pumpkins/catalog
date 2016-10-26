<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\View;

use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\Product;

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
        $stubProductImageFileLocator = $this->createMock(ProductImageFileLocator::class);
        $this->locator = new IntegrationTestProductViewLocator($stubProductImageFileLocator);
    }

    public function testProductViewInterfaceIsImplemented()
    {
        $this->assertInstanceOf(ProductViewLocator::class, $this->locator);
    }

    public function testProductViewIsReturned()
    {
        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->createMock(Product::class);

        $result = $this->locator->createForProduct($stubProduct);

        $this->assertInstanceOf(ProductView::class, $result);
    }

    public function testItReturnsAConfigurableProductViewForConfigurableProducts()
    {
        /** @var ConfigurableProduct|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->createMock(ConfigurableProduct::class);

        $result = $this->locator->createForProduct($stubProduct);

        $this->assertInstanceOf(CompositeProductView::class, $result);
    }
}
