<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Product\Image\ProductImageListBuilder;
use LizardsAndPumpkins\Import\Product\Image\ProductImageList;
use LizardsAndPumpkins\Import\Tax\ProductTaxClass;
use PHPUnit\Framework\TestCase;

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
class SimpleProductBuilderTest extends TestCase
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

    private function createProductAttribute(string $code, string $value) : ProductAttribute
    {
        return new ProductAttribute(AttributeCode::fromString($code), $value, []);
    }

    public function setUp()
    {
        $this->stubProductId = $this->createMock(ProductId::class);
        $this->mockProductAttributeListBuilder = $this->createMock(ProductAttributeListBuilder::class);
        $this->mockProductImageListBuilder = $this->createMock(ProductImageListBuilder::class);

        $this->mockAttributeList = $this->createMock(ProductAttributeList::class);
        $this->mockProductAttributeListBuilder->method('getAttributeListForContext')
            ->willReturn($this->mockAttributeList);

        $this->mockProductImageListBuilder->method('getImageListForContext')
            ->willReturn($this->createMock(ProductImageList::class));

        $stubTaxClass = $this->createMock(ProductTaxClass::class);
        
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
        $stubContext = $this->createMock(Context::class);
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

        $stubContext = $this->createMock(Context::class);
        $product = $this->productBuilder->getProductForContext($stubContext);

        array_map(function ($priceAttributeCode) use ($product) {
            $price = $product->getFirstValueOfAttribute($priceAttributeCode);
            $this->assertInternalType('integer', $price);
        }, $priceAttributeCodes);
    }

    public function testProductIsAvailableForContextIfAttributesCanBeCollected()
    {
        $this->mockAttributeList->method('count')->willReturn(2);
        $stubContext = $this->createMock(Context::class);
        
        $this->assertTrue($this->productBuilder->isAvailableForContext($stubContext));
    }

    public function testProductIsNotAvailableForContextIfNoAttributesCanBeCollected()
    {
        $this->mockAttributeList->method('count')->willReturn(0);
        $stubContext = $this->createMock(Context::class);
        
        $this->assertFalse($this->productBuilder->isAvailableForContext($stubContext));
    }
}
