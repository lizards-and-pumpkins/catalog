<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\View;

use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\Product;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Product\View\IntegrationTestProductViewLocator
 * @uses   \LizardsAndPumpkins\Import\Product\View\IntegrationTestProductView
 * @uses   \LizardsAndPumpkins\Import\Product\View\IntegrationTestConfigurableProductView
 */
class IntegrationTestProductViewLocatorTest extends TestCase
{
    /**
     * @var IntegrationTestProductViewLocator
     */
    private $locator;

    final protected function setUp(): void
    {
        /** @var ProductImageFileLocator|MockObject $stubProductImageFileLocator */
        $stubProductImageFileLocator = $this->createMock(ProductImageFileLocator::class);
        $this->locator = new IntegrationTestProductViewLocator($stubProductImageFileLocator);
    }

    public function testProductViewInterfaceIsImplemented(): void
    {
        $this->assertInstanceOf(ProductViewLocator::class, $this->locator);
    }

    public function testProductViewIsReturned(): void
    {
        /** @var Product|MockObject $stubProduct */
        $stubProduct = $this->createMock(Product::class);

        $result = $this->locator->createForProduct($stubProduct);

        $this->assertInstanceOf(ProductView::class, $result);
    }

    public function testItReturnsAConfigurableProductViewForConfigurableProducts(): void
    {
        /** @var ConfigurableProduct|MockObject $stubProduct */
        $stubProduct = $this->createMock(ConfigurableProduct::class);

        $result = $this->locator->createForProduct($stubProduct);

        $this->assertInstanceOf(CompositeProductView::class, $result);
    }
}
