<?php

namespace Brera\PoC\Tests\Unit;

use Brera\PoC\Product\Product,
    Brera\Poc\Product\ProductId;

/**
 * Class ProductTest
 * @package Brera\PoC
 * @covers \Brera\PoC\Product
 */
class ProductTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $testName = 'test';
    
    /**
     * @var ProductId|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductId;
    
    /**
     * @var Product
     */
    private $product;
    
    public function setUp()
    {
        $this->stubProductId = $this->getMockBuilder(ProductId::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->product = new Product($this->stubProductId, $this->testName);
    }

    /**
     * @test
     */
    public function itShouldReturnTheProductId()
    {
        $result = $this->product->getId();
        $this->assertSame($this->stubProductId, $result);
    }

    /**
     * @test
     */
    public function itShouldReturnTheName()
    {
        $this->assertSame($this->testName, $this->product->getName());
    }
} 
