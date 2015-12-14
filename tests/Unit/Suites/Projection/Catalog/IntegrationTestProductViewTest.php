<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Product\Product;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\IntegrationTestProductView
 */
class IntegrationTestProductViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProduct;

    /**
     * @var IntegrationTestProductView
     */
    private $productView;

    protected function setUp()
    {
        $this->mockProduct = $this->getMock(Product::class);
        $this->productView = new IntegrationTestProductView($this->mockProduct);
    }

    public function testOriginalProductIsReturned()
    {
        $this->assertSame($this->mockProduct, $this->productView->getOriginalProduct());
    }
}
