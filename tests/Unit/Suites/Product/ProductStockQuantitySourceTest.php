<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\ProductStockQuantitySource
 */
class ProductStockQuantitySourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductId|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductId;

    /**
     * @var string[]|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContextData = ['foo' => 'bar', 'baz' => 'qux'];

    /**
     * @var Quantity|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubQuantity;

    /**
     * @var ProductStockQuantitySource
     */
    private $productStockQuantitySource;

    protected function setUp()
    {
        $this->stubProductId = $this->getMock(ProductId::class, [], [], '', false);
        $this->stubQuantity = $this->getMock(Quantity::class);

        $this->productStockQuantitySource = new ProductStockQuantitySource(
            $this->stubProductId,
            $this->stubContextData,
            $this->stubQuantity
        );
    }

    public function testProductIdIsReturned()
    {
        $result = $this->productStockQuantitySource->getProductId();
        $this->assertEquals($this->stubProductId, $result);
    }

    public function testProductStockQuantityContextDataIsReturned()
    {
        $result = $this->productStockQuantitySource->getContextData();
        $this->assertEquals($this->stubContextData, $result);
    }

    public function testStockIsReturned()
    {
        $result = $this->productStockQuantitySource->getStock();
        $this->assertEquals($this->stubQuantity, $result);
    }
}
