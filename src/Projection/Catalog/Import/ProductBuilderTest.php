<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductAttributeList;
use LizardsAndPumpkins\Product\ProductAttributeListBuilder;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Product\ProductImageList;

/**
 * @covers \LizardsAndPumpkins\Product\ProductBuilder
 * @uses   \LizardsAndPumpkins\Product\Product
 * @uses   \LizardsAndPumpkins\Product\ProductAttributeListBuilder
 * @uses   \LizardsAndPumpkins\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Product\ProductImageList
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\ProductImageListBuilder
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

    /**
     * @var ProductImageListBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductImageListBuilder;

    public function setUp()
    {
        $this->stubProductId = $this->getMock(ProductId::class, [], [], '', false);
        $this->mockProductAttributeListBuilder = $this->getMockBuilder(ProductAttributeListBuilder::class)
            ->setMethods(['getAttributeListForContext'])
            ->getMock();

        $this->mockProductImageListBuilder = $this->getMock(ProductImageListBuilder::class);

        $this->productBuilder = new ProductBuilder(
            $this->stubProductId,
            $this->mockProductAttributeListBuilder,
            $this->mockProductImageListBuilder
        );
    }

    public function testProductIdIsReturned()
    {
        $result = $this->productBuilder->getId();
        $this->assertSame($this->stubProductId, $result);
    }

    public function testProductForContextIsReturned()
    {
        $stubContext = $this->getMock(Context::class);
        
        $this->mockProductAttributeListBuilder->method('getAttributeListForContext')
            ->with($stubContext)
            ->willReturn($this->getMock(ProductAttributeList::class));

        $this->mockProductImageListBuilder->method('getImageListForContext')
            ->with($stubContext)
            ->willReturn($this->getMock(ProductImageList::class));

        $result = $this->productBuilder->getProductForContext($stubContext);
        $this->assertInstanceOf(Product::class, $result);
    }
}
