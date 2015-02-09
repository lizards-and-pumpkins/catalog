<?php

namespace Brera\Product;

use Brera\Environment\Environment;

/**
 * @covers \Brera\Product\ProductSource
 */
class ProductSourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductId|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductId;

    /**
     * @var ProductSource
     */
    private $productSource;

    /**
     * @var ProductAttributeList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductAttributeList;

    public function setUp()
    {
        $this->stubProductId = $this->getMockBuilder(ProductId::class)
        ->disableOriginalConstructor()
        ->getMock();
        $this->mockProductAttributeList = $this->getMock(ProductAttributeList::class, ['getAttributesForEnvironment']);

        $this->productSource = new ProductSource($this->stubProductId, $this->mockProductAttributeList);
    }

    /**
     * @test
     */
    public function itShouldReturnTheProductId()
    {
        $result = $this->productSource->getId();
        $this->assertSame($this->stubProductId, $result);
    }

    /**
     * @test
     */
    public function itShouldReturnAProductForAnEnvironment()
    {
        /** @var Environment|\PHPUnit_Framework_MockObject_MockObject $stubEnvironment */
        $stubEnvironment = $this->getMock(Environment::class);
        $this->mockProductAttributeList->expects($this->once())
        ->method('getAttributesForEnvironment')
        ->with($stubEnvironment)
        ->willReturn($this->mockProductAttributeList);
        $result = $this->productSource->getProductForEnvironment($stubEnvironment);
        $this->assertInstanceOf(Product::class, $result);
    }
}
