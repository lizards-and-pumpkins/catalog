<?php

namespace Brera\Product;

use Brera\Context\Context;

/**
 * @covers \Brera\Product\ProductStockQuantitySource
 */
class ProductStockQuantitySourceTest extends \PHPUnit_Framework_TestCase
{
    public function testProductSkuIsReturned()
    {
        $stubSku = $this->getMock(Sku::class);
        $stubContext = $this->getMock(Context::class);
        $stubQuantity = $this->getMock(Quantity::class);

        $source = new ProductStockQuantitySource($stubSku, $stubContext, $stubQuantity);
        $result = $source->getSku();

        $this->assertEquals($stubSku, $result);
    }

    public function testProductStockQuantityContextDataIsReturned()
    {
        $stubSku = $this->getMock(Sku::class);
        $stubContext = $this->getMock(Context::class);
        $stubQuantity = $this->getMock(Quantity::class);

        $source = new ProductStockQuantitySource($stubSku, $stubContext, $stubQuantity);
        $result = $source->getContext();

        $this->assertEquals($stubContext, $result);
    }

    public function testStockIsReturned()
    {
        $stubSku = $this->getMock(Sku::class);
        $stubContext = $this->getMock(Context::class);
        $stubQuantity = $this->getMock(Quantity::class);

        $source = new ProductStockQuantitySource($stubSku, $stubContext, $stubQuantity);
        $result = $source->getStock();

        $this->assertEquals($stubQuantity, $result);
    }
}
