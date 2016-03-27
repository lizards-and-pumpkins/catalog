<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\Import\Product\Image\ProductImageListBuilder;
use LizardsAndPumpkins\Import\Product\ProductAttribute;
use LizardsAndPumpkins\Import\Product\ProductAttributeListBuilder;
use LizardsAndPumpkins\Import\Product\SimpleProduct;
use LizardsAndPumpkins\Import\Product\ProductAttributeList;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\Import\Product\Image\ProductImageList;
use LizardsAndPumpkins\Import\Product\SimpleProductBuilder;
use LizardsAndPumpkins\Import\Tax\ProductTaxClass;

/**
 * @covers \LizardsAndPumpkins\Import\Product\SimpleProductBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeListBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImageListBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\SimpleProduct
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttribute
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImageList
 * @uses   \LizardsAndPumpkins\Import\Price\Price
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

        $stubTaxClass = $this->getMock(ProductTaxClass::class, [], [], '', false);
        
        $this->productBuilder = new SimpleProductBuilder(
            $this->stubProductId,
            $stubTaxClass,
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

        array_map(function ($priceAttributeCode) use ($product) {
            $price = $product->getFirstValueOfAttribute($priceAttributeCode);
            $this->assertInternalType('integer', $price);
        }, $priceAttributeCodes);
    }

    public function testProductIsAvailableForContextIfAttributesCanBeCollected()
    {
        $this->mockAttributeList->method('count')->willReturn(2);
        $stubContext = $this->getMock(Context::class);
        
        $this->assertTrue($this->productBuilder->isAvailableForContext($stubContext));
    }

    public function testProductIsNotAvailableForContextIfNoAttributesCanBeCollected()
    {
        $this->mockAttributeList->method('count')->willReturn(0);
        $stubContext = $this->getMock(Context::class);
        
        $this->assertFalse($this->productBuilder->isAvailableForContext($stubContext));
    }
}
