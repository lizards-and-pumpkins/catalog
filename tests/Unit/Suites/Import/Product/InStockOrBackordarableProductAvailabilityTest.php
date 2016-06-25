<?php

namespace LizardsAndPumpkins\Import\Product;

/**
 * @covers \LizardsAndPumpkins\Import\Product\InStockOrBackordarableProductAvailability
 */
class InStockOrBackordarableProductAvailabilityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InStockOrBackordarableProductAvailability
     */
    private $inStockOrBackordarableProductAvailability;

    protected function setUp()
    {
        $this->inStockOrBackordarableProductAvailability = new InStockOrBackordarableProductAvailability();
    }

    public function testProductAvailabilityInterfaceIsImplemented()
    {
        $this->assertInstanceOf(ProductAvailability::class, $this->inStockOrBackordarableProductAvailability);
    }

    public function testFalseIsReturnedIfProductIsNotInStockAndNotAvailableForBackorders()
    {
        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->createMock(Product::class);
        $stubProduct->method('getFirstValueOfAttribute')->willReturnMap([['backorders', 'false'], ['stock_qty', '0']]);

        $this->assertFalse($this->inStockOrBackordarableProductAvailability->isProductSalable($stubProduct));
    }

    public function testTrueIsReturnedIfProductIsInStockAndNotAvailableForBackorders()
    {
        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->createMock(Product::class);
        $stubProduct->method('getFirstValueOfAttribute')->willReturnMap([['backorders', 'false'], ['stock_qty', '1']]);

        $this->assertTrue($this->inStockOrBackordarableProductAvailability->isProductSalable($stubProduct));
    }

    public function testTrueIsReturnedIfProductIsNotInStockAndAvailableForBackorders()
    {
        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->createMock(Product::class);
        $stubProduct->method('getFirstValueOfAttribute')->willReturnMap([['backorders', 'true'], ['stock_qty', '0']]);

        $this->assertTrue($this->inStockOrBackordarableProductAvailability->isProductSalable($stubProduct));
    }

    public function testTrueIsReturnedIfProductIsInStockAndAvailableForBackorders()
    {
        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $stubProduct */
        $stubProduct = $this->createMock(Product::class);
        $stubProduct->method('getFirstValueOfAttribute')->willReturnMap([['backorders', 'true'], ['stock_qty', '1']]);

        $this->assertTrue($this->inStockOrBackordarableProductAvailability->isProductSalable($stubProduct));
    }
}
