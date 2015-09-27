<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;

/**
 * @covers \LizardsAndPumpkins\Product\ProductSource
 * @uses   \LizardsAndPumpkins\Product\Product
 * @uses   \LizardsAndPumpkins\Product\ProductAttributeList
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
        $this->stubProductId = $this->getMock(ProductId::class, [], [], '', false);
        $this->mockProductAttributeList = $this->getMockBuilder(ProductAttributeList::class)
            ->setMethods(['getAttributeListForContext'])
            ->getMock();

        $this->productSource = new ProductSource($this->stubProductId, $this->mockProductAttributeList);
    }

    public function testProductIdIsReturned()
    {
        $result = $this->productSource->getId();
        $this->assertSame($this->stubProductId, $result);
    }

    public function testItReturnsTheAttributeList()
    {
        $this->assertSame($this->mockProductAttributeList, $this->productSource->getAttributeList());
    }

    public function testProductForContextIsReturned()
    {
        $stubContext = $this->getMock(Context::class);
        $this->mockProductAttributeList->method('getAttributeListForContext')
            ->with($stubContext)
            ->willReturn($this->mockProductAttributeList);
        $result = $this->productSource->getProductForContext($stubContext);
        $this->assertInstanceOf(Product::class, $result);
    }
}
