<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\SimpleProduct;
use LizardsAndPumpkins\Product\ProductAttributeList;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Product\ProductImageList;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\Import\SimpleProductBuilder
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\ProductAttributeListBuilder
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\ProductImageListBuilder
 * @uses   \LizardsAndPumpkins\Product\SimpleProduct
 * @uses   \LizardsAndPumpkins\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Product\ProductImageList
 */
class SimpleProductBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductId|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductId;

    /**
     * @var SimpleProductBuilder
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
        $this->mockProductAttributeListBuilder = $this->getMock(ProductAttributeListBuilder::class);
        $this->mockProductImageListBuilder = $this->getMock(ProductImageListBuilder::class);
        
        $this->productBuilder = new SimpleProductBuilder(
            $this->stubProductId,
            $this->mockProductAttributeListBuilder,
            $this->mockProductImageListBuilder
        );
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
        $this->assertInstanceOf(SimpleProduct::class, $result);
    }
}
