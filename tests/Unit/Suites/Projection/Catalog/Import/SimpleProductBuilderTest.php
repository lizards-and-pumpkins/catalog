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

    /**
     * @param string $code
     * @param string $value
     * @return ProductAttribute
     */
    private function createProductAttribute($code, $value)
    {
        return new ProductAttribute(AttributeCode::fromString($code), $value, []);
    }

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
        $this->mockAttributeList->method('getAllAttributes')->willReturn([]);
        $stubContext = $this->getMock(Context::class);
        $result = $this->productBuilder->getProductForContext($stubContext);

        $this->assertInstanceOf(SimpleProduct::class, $result);
    }

    public function testProductPriceAttributeIsInteger()
    {
        $sourcePrice = '11.99';
        $expectedPrice = 1199;
        $priceAttributeCodes = ['price', 'special_price'];

        $sourcePriceAttribute = $this->createProductAttribute('price', $sourcePrice);
        $sourceSpecialPriceAttribute = $this->createProductAttribute('special_price', $sourcePrice);

        $this->mockAttributeList->method('getAllAttributes')->willReturn([
            $sourcePriceAttribute,
            $sourceSpecialPriceAttribute
        ]);
        $this->mockAttributeList->method('hasAttribute')->willReturn(true);
        $this->mockAttributeList->method('getAttributesWithCode')->willReturnMap([
            ['price', [$sourcePriceAttribute]],
            ['special_price', [$sourceSpecialPriceAttribute]],
        ]);

        $stubContext = $this->getMock(Context::class);
        $product = $this->productBuilder->getProductForContext($stubContext);

        array_map(function ($priceAttributeCode) use ($product, $expectedPrice) {
            $price = $product->getFirstValueOfAttribute($priceAttributeCode);
            $this->assertInternalType('integer', $price);
            $this->assertSame($expectedPrice, $price);
        }, $priceAttributeCodes);
    }
}
