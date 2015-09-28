<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;

/**
 * @covers \LizardsAndPumpkins\Product\ProductBuilder
 * @uses   \LizardsAndPumpkins\Product\Product
 * @uses   \LizardsAndPumpkins\Product\ProductAttributeListBuilder
 * @uses   \LizardsAndPumpkins\Product\ProductAttributeList
 */
class ProductBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductId|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductId;

    /**
     * @var ProductBuilder
     */
    private $productBuilder;

    /**
     * @var ProductAttributeListBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductAttributeListBuilder;

    public function setUp()
    {
        $this->stubProductId = $this->getMock(ProductId::class, [], [], '', false);
        $this->mockProductAttributeListBuilder = $this->getMockBuilder(ProductAttributeListBuilder::class)
            ->setMethods(['getAttributeListForContext'])
            ->getMock();

        $this->productBuilder = new ProductBuilder($this->stubProductId, $this->mockProductAttributeListBuilder);
    }

    public function testProductIdIsReturned()
    {
        $result = $this->productBuilder->getId();
        $this->assertSame($this->stubProductId, $result);
    }

    public function testItReturnsTheAttributeList()
    {
        $this->assertSame($this->mockProductAttributeListBuilder, $this->productBuilder->getAttributeListBuilder());
    }

    public function testProductForContextIsReturned()
    {
        $stubContext = $this->getMock(Context::class);
        $this->mockProductAttributeListBuilder->method('getAttributeListForContext')
            ->with($stubContext)
            ->willReturn($this->getMock(ProductAttributeList::class));
        $result = $this->productBuilder->getProductForContext($stubContext);
        $this->assertInstanceOf(Product::class, $result);
    }
}
