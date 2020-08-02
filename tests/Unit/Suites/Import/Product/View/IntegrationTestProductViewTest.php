<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\View;

use LizardsAndPumpkins\Import\Product\Product;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Product\View\IntegrationTestProductView
 */
class IntegrationTestProductViewTest extends TestCase
{
    /**
     * @var Product|MockObject
     */
    private $mockProduct;

    /**
     * @var IntegrationTestProductView
     */
    private $productView;

    final protected function setUp(): void
    {
        $this->mockProduct = $this->createMock(Product::class);
        $stubProductImageFileLocator = $this->createMock(ProductImageFileLocator::class);
        $this->productView = new IntegrationTestProductView($this->mockProduct, $stubProductImageFileLocator);
    }

    public function testOriginalProductIsReturned(): void
    {
        $this->assertSame($this->mockProduct, $this->productView->getOriginalProduct());
    }
}
