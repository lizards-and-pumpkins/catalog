<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\Product\ProductAttribute;
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
 * @uses   \LizardsAndPumpkins\Product\ProductAttribute
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Product\ProductImageList
 * @uses   \LizardsAndPumpkins\Product\Price
 */
class SimpleProductBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductAttributeList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockAttributeList;
    
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
        
        $this->mockAttributeList = $this->getMock(ProductAttributeList::class);
        $this->mockProductAttributeListBuilder->method('getAttributeListForContext')
            ->willReturn($this->mockAttributeList);

        $this->mockProductImageListBuilder->method('getImageListForContext')
            ->willReturn($this->getMock(ProductImageList::class));
        
        $this->productBuilder = new SimpleProductBuilder(
            $this->stubProductId,
            $this->mockProductAttributeListBuilder,
            $this->mockProductImageListBuilder
        );
    }

    public function testProductForContextIsReturned()
    {
        $this->mockAttributeList->method('getAttributeCodes')->willReturn([]);
        $stubContext = $this->getMock(Context::class);
        $result = $this->productBuilder->getProductForContext($stubContext);
        
        $this->assertInstanceOf(SimpleProduct::class, $result);
    }

    public function testProductPriceAttributeIsInteger()
    {
        $sourcePrice = '11.99';
        $expectedPrice = 1199;
        
        $sourcePriceAttribute = new ProductAttribute(AttributeCode::fromString('price'), $sourcePrice, []);
        
        $this->mockAttributeList->method('getAttributeCodes')->willReturn(['price']);
        $this->mockAttributeList->method('hasAttribute')->with('price')->willReturn(true);
        $this->mockAttributeList->method('getAttributesWithCode')->with('price')->willReturn([$sourcePriceAttribute]);

        $stubContext = $this->getMock(Context::class);
        $product = $this->productBuilder->getProductForContext($stubContext);
        $price = $product->getFirstValueOfAttribute('price');
        
        $this->assertInternalType('integer', $price);
        $this->assertSame($expectedPrice, $price);
    }
}
