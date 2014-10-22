<?php

namespace Brera\PoC\Product;

/**
 * @covers \Brera\PoC\Product\ProductId
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

    public function setUp()
    {
        $this->stubSku = $this->getMock(Sku::class);
        $this->productId = ProductId::fromSku($this->stubSku);
    }

    /**
     * @test
     */
    public function itCanBeCreatedFromSku()
    {
        $this->assertInstanceOf(ProductId::class, $this->productId);
    }

    /**
     * @test
     */
    public function itCanBeConvertedToString()
    {
        $result = (string) $this->productId;
        $this->assertInternalType('string', $result);
    }
} 
