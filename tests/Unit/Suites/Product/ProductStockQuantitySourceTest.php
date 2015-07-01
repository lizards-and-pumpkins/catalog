<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\ProjectionSourceData;

/**
 * @covers \Brera\Product\ProductStockQuantitySource
 */
class ProductStockQuantitySourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Sku|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSku;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

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
        $this->stubSku = $this->getMock(Sku::class);
        $this->stubContext = $this->getMock(Context::class);
        $this->stubQuantity = $this->getMock(Quantity::class);

        $this->productStockQuantitySource = new ProductStockQuantitySource(
            $this->stubSku,
            $this->stubContext,
            $this->stubQuantity
        );
    }

    public function testProjectionSourceDataInterfaceIsImplemented()
    {
        $this->assertInstanceOf(ProjectionSourceData::class, $this->productStockQuantitySource);
    }

    public function testProductSkuIsReturned()
    {
        $result = $this->productStockQuantitySource->getSku();
        $this->assertEquals($this->stubSku, $result);
    }

    public function testProductStockQuantityContextDataIsReturned()
    {
        $result = $this->productStockQuantitySource->getContext();
        $this->assertEquals($this->stubContext, $result);
    }

    public function testStockIsReturned()
    {
        $result = $this->productStockQuantitySource->getStock();
        $this->assertEquals($this->stubQuantity, $result);
    }
}
