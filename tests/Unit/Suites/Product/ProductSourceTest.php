<?php

namespace Brera\Product;

use Brera\Context\Context;

/**
 * @covers \Brera\Product\ProductSource
 * @uses \Brera\Product\Product
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
        $this->mockProductAttributeList = $this->getMock(ProductAttributeList::class, ['getAttributesForContext']);

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
    public function itShouldReturnAProductForAnContext()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);
        $this->mockProductAttributeList->expects($this->once())
        ->method('getAttributesForContext')
        ->with($stubContext)
        ->willReturn($this->mockProductAttributeList);
        $result = $this->productSource->getProductForContext($stubContext);
        $this->assertInstanceOf(Product::class, $result);
    }
}
