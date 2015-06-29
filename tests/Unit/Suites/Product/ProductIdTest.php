<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\ProductId
 */
class ProductIdTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductId
     */
    private $productId;

    /**
     * @var Sku|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSku;

    protected function setUp()
    {
        $this->stubSku = $this->getMock(Sku::class);
        $this->productId = ProductId::fromSku($this->stubSku);
    }

    public function testCanBeCreatedFromSku()
    {
        $this->assertInstanceOf(ProductId::class, $this->productId);
    }

    public function testCanBeConvertedToString()
    {
        $result = (string) $this->productId;
        $this->assertInternalType('string', $result);
    }
}
