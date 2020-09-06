<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Product\Image\ProductImageListBuilder;
use LizardsAndPumpkins\Import\Product\Image\ProductImageList;
use LizardsAndPumpkins\Import\Tax\ProductTaxClass;
use PHPUnit\Framework\MockObject\MockObject;
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
     * @var ProductAttributeList|MockObject
     */
    private $mockAttributeList;

    /**
     * @var SimpleProductBuilder
     */
    private $productBuilder;

    private function createProductAttribute(string $code, string $value) : ProductAttribute
    {
        return new ProductAttribute(AttributeCode::fromString($code), $value, []);
    }

    final protected function setUp(): void
    {
        $stubProductId = $this->createMock(ProductId::class);
        $mockProductAttributeListBuilder = $this->createMock(ProductAttributeListBuilder::class);
        $mockProductImageListBuilder = $this->createMock(ProductImageListBuilder::class);

        $this->mockAttributeList = $this->createMock(ProductAttributeList::class);
        $mockProductAttributeListBuilder->method('getAttributeListForContext')
            ->willReturn($this->mockAttributeList);

        $mockProductImageListBuilder->method('getImageListForContext')
            ->willReturn($this->createMock(ProductImageList::class));

        /** @var ProductTaxClass|MockObject $stubTaxClass */
        $stubTaxClass = $this->createMock(ProductTaxClass::class);
        
        $this->productBuilder = new SimpleProductBuilder(
            $stubProductId,
            $stubTaxClass,
            $mockProductAttributeListBuilder,
            $mockProductImageListBuilder
        );
    }

    public function testProductForContextIsReturned(): void
    {
        $this->mockAttributeList->method('getAllAttributes')->willReturn([]);
        /** @var Context|MockObject $stubContext */
        $stubContext = $this->createMock(Context::class);
        $result = $this->productBuilder->getProductForContext($stubContext);

        $this->assertInstanceOf(SimpleProduct::class, $result);
    }

    public function testProductPriceAttributeIsInteger(): void
    {
        $sourcePrice = '11.99';
        $priceAttributeCodes = ['price', 'special_price'];

        $sourcePriceAttribute = $this->createProductAttribute('price', $sourcePrice);
        $sourceSpecialPriceAttribute = $this->createProductAttribute('special_price', $sourcePrice);

        $this->mockAttributeList->method('getAllAttributes')->willReturn([
            $sourcePriceAttribute,
            $sourceSpecialPriceAttribute
        ]);

        /** @var Context|MockObject $stubContext */
        $stubContext = $this->createMock(Context::class);
        $product = $this->productBuilder->getProductForContext($stubContext);

        array_map(function ($priceAttributeCode) use ($product) {
            $price = $product->getFirstValueOfAttribute($priceAttributeCode);
            $this->assertIsInt($price);
        }, $priceAttributeCodes);
    }

    public function testProductIsAvailableForContextIfAttributesCanBeCollected(): void
    {
        $this->mockAttributeList->method('count')->willReturn(2);
        /** @var Context|MockObject $stubContext */
        $stubContext = $this->createMock(Context::class);
        
        $this->assertTrue($this->productBuilder->isAvailableForContext($stubContext));
    }

    public function testProductIsNotAvailableForContextIfNoAttributesCanBeCollected(): void
    {
        $this->mockAttributeList->method('count')->willReturn(0);
        /** @var Context|MockObject $stubContext */
        $stubContext = $this->createMock(Context::class);
        
        $this->assertFalse($this->productBuilder->isAvailableForContext($stubContext));
    }

    public function testProductHasNoSpecialPriceAttributeIfEmptyStringIsDefined(): void
    {
        $sourceSpecialPriceAttribute = $this->createProductAttribute('special_price', '');
        $this->mockAttributeList->method('getAllAttributes')->willReturn([$sourceSpecialPriceAttribute]);

        /** @var Context|MockObject $stubContext */
        $stubContext = $this->createMock(Context::class);
        $product = $this->productBuilder->getProductForContext($stubContext);

        $this->assertFalse($product->hasAttribute(AttributeCode::fromString('special_price')));
    }
}
