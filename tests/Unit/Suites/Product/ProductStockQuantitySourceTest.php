<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\ProductStockQuantitySource
 */
class ProductStockQuantitySourceTest extends \PHPUnit_Framework_TestCase
{
    public function testProductSkuIsReturned()
    {
        $stubSku = $this->getMock(Sku::class);
        $stubContextData = [];
        $stubQuantity = 0;

        $source = new ProductStockQuantitySource($stubSku, $stubContextData, $stubQuantity);
        $result = $source->getSku();

        $this->assertEquals($stubSku, $result);
    }

    public function testProductStockQuantityContextDataIsReturned()
    {
        $stubSku = $this->getMock(Sku::class);
        $stubContextData = ['foo' => 'bar', 'baz' => 'qux'];
        $stubQuantity = 0;

        $source = new ProductStockQuantitySource($stubSku, $stubContextData, $stubQuantity);
        $result = $source->getContextData();

        $this->assertEquals($stubContextData, $result);
    }

    public function testQuantityIsReturned()
    {
        $stubSku = $this->getMock(Sku::class);
        $stubContextData = ['foo' => 'bar', 'baz' => 'qux'];
        $stubQuantity = 1;

        $source = new ProductStockQuantitySource($stubSku, $stubContextData, $stubQuantity);
        $result = $source->getQuantity();

        $this->assertEquals($stubQuantity, $result);
    }
}
