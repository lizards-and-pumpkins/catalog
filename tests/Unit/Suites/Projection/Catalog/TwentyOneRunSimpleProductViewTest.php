<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductAttribute;
use LizardsAndPumpkins\Product\ProductAttributeList;
use LizardsAndPumpkins\Product\ProductImage\ProductImageFileLocator;
use LizardsAndPumpkins\Utils\ImageStorage\Image;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\TwentyOneRunSimpleProductView
 * @uses   \LizardsAndPumpkins\Projection\Catalog\AbstractProductView
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Product\ProductAttribute
 * @uses   \LizardsAndPumpkins\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Product\ProductImage\ProductImageList
 */
class TwentyOneRunSimpleProductViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProduct;

    /**
     * @var TwentyOneRunSimpleProductView
     */
    private $productView;

    /**
     * @var ProductImageFileLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProductImageFileLocator;

    protected function setUp()
    {
        $this->mockProduct = $this->getMock(Product::class);
        $this->mockProductImageFileLocator = $this->getMock(ProductImageFileLocator::class);
        $this->mockProductImageFileLocator->method('getPlaceholder')->willReturn($this->getMock(Image::class));
        $this->mockProductImageFileLocator->method('getVariantCodes')->willReturn(['large']);
        $this->productView = new TwentyOneRunSimpleProductView($this->mockProduct, $this->mockProductImageFileLocator);
    }

    public function testOriginalProductIsReturned()
    {
        $this->assertSame($this->mockProduct, $this->productView->getOriginalProduct());
    }

    public function testProductViewInterfaceIsImplemented()
    {
        $this->assertInstanceOf(ProductView::class, $this->productView);
    }

    public function testGettingFirstValueOfBackordersAttributeReturnsEmptyString()
    {
        $testAttributeCode = 'backorders';
        $testAttributeValue = true;

        $attribute = new ProductAttribute($testAttributeCode, $testAttributeValue, []);
        $attributeList = new ProductAttributeList($attribute);
        $this->mockProduct->method('getAttributes')->willReturn($attributeList);

        $this->assertSame('', $this->productView->getFirstValueOfAttribute($testAttributeCode));
    }

    public function testGettingAllValuesOfBackordersAttributeReturnsEmptyArray()
    {
        $testAttributeCode = 'backorders';
        $testAttributeValue = true;

        $attribute = new ProductAttribute($testAttributeCode, $testAttributeValue, []);
        $attributeList = new ProductAttributeList($attribute);
        $this->mockProduct->method('getAttributes')->willReturn($attributeList);

        $this->assertSame([], $this->productView->getAllValuesOfAttribute($testAttributeCode));
    }

    public function testProductViewAttributeListDoesNotHaveBackorders()
    {
        $testAttributeCode = 'backorders';
        $testAttributeValue = true;

        $attribute = new ProductAttribute($testAttributeCode, $testAttributeValue, []);
        $attributeList = new ProductAttributeList($attribute);
        $this->mockProduct->method('getAttributes')->willReturn($attributeList);

        $this->assertFalse($this->productView->hasAttribute($testAttributeCode));
    }

    public function testProductJsonDoesNotHaveBackorders()
    {

        $testAttributeCode = 'backorders';
        $testAttributeValue = true;

        $attribute = new ProductAttribute($testAttributeCode, $testAttributeValue, []);
        $attributeList = new ProductAttributeList($attribute);
        $this->mockProduct->method('getAttributes')->willReturn($attributeList);
        $this->mockProduct->method('jsonSerialize')->willReturn(['attributes' => $attributeList]);

        $productData = json_decode(json_encode($this->productView), true);

        $this->assertArrayNotHasKey('backorders', $productData['attributes']);
    }

    public function testFilteredProductAttributeListIsReturned()
    {
        $nonPriceAttribute = new ProductAttribute('foo', 'bar', []);
        $priceAttribute = new ProductAttribute('price', 1000, []);
        $specialPriceAttribute = new ProductAttribute('special_price', 900, []);
        $backordersAttribute = new ProductAttribute('backorders', true, []);

        $attributeList = new ProductAttributeList(
            $nonPriceAttribute,
            $priceAttribute,
            $specialPriceAttribute,
            $backordersAttribute
        );

        $this->mockProduct->method('getAttributes')->willReturn($attributeList);

        $result = $this->productView->getAttributes();

        $this->assertCount(1, $result);
        $this->assertContains($nonPriceAttribute, $result->getAllAttributes());
    }

    public function testMaximumPurchasableQuantityIsReturnedIfProductIsAvailableForBackorders()
    {
        $stockAttributeCode = 'stock_qty';
        $testAttributeValue = 1;

        $stockQtyAttribute = new ProductAttribute($stockAttributeCode, $testAttributeValue, []);
        $backordersAttribute = new ProductAttribute('backorders', 'true', []);
        $attributeList = new ProductAttributeList($stockQtyAttribute, $backordersAttribute);

        $this->mockProduct->method('getAttributes')->willReturn($attributeList);
        $this->mockProduct->method('getFirstValueOfAttribute')->with('backorders')->willReturn('true');
        $result = $this->productView->getFirstValueOfAttribute($stockAttributeCode);

        $this->assertSame(TwentyOneRunSimpleProductView::MAX_PURCHASABLE_QTY, $result);
    }

    public function testMaximumPurchasableQuantityIsReturnedIfProductQuantityIsGreaterThanMaximumPurchasableQuantity()
    {
        $stockAttributeCode = 'stock_qty';
        $testAttributeValue = 6;

        $attribute = new ProductAttribute($stockAttributeCode, $testAttributeValue, []);
        $attributeList = new ProductAttributeList($attribute);

        $this->mockProduct->method('getAttributes')->willReturn($attributeList);
        $result = $this->productView->getFirstValueOfAttribute($stockAttributeCode);

        $this->assertSame(TwentyOneRunSimpleProductView::MAX_PURCHASABLE_QTY, $result);
    }

    public function testItReturnsTheOriginalStockQtyIfBackordersIsFalseAndQtyIsSmallerThanMaximumPurchasableQuantity()
    {
        $stockAttributeCode = 'stock_qty';
        $testAttributeValue = 4;

        $attribute = new ProductAttribute($stockAttributeCode, $testAttributeValue, []);
        $attributeList = new ProductAttributeList($attribute);

        $this->mockProduct->method('getAttributes')->willReturn($attributeList);
        $this->mockProduct->method('getFirstValueOfAttribute')->with('backorders')->willReturn('false');
        $result = $this->productView->getFirstValueOfAttribute($stockAttributeCode);

        $this->assertSame($testAttributeValue, $result);
    }

    public function testItUsesTheInjectedProductImageFileLocatorToGetPlaceholderImages()
    {
        $stubAttributeList = $this->getMock(ProductAttributeList::class);
        $stubAttributeList->method('getAllAttributes')->willReturn([]);
        $this->mockProduct->method('getAttributes')->willReturn($stubAttributeList);
        $this->mockProduct->method('jsonSerialize')->willReturn(['images' => []]);
        $this->mockProduct->method('getImages')->willReturn(new \ArrayIterator([]));
        $this->mockProduct->method('getContext')->willReturn($this->getMock(Context::class));

        $this->mockProductImageFileLocator->expects($this->once())->method('getPlaceholder');
        json_encode($this->productView);
    }

    /**
     * @dataProvider requiredAttributeCodeProvider
     * @param string $requiredAttributeCode
     */
    public function testProductTitleContainsRequiredAttributes($requiredAttributeCode)
    {
        $testAttributeValue = 'foo';
        $attributeCode = AttributeCode::fromString($requiredAttributeCode);
        $attributeList = ProductAttributeList::fromArray([
            [
                ProductAttribute::CODE => $attributeCode,
                ProductAttribute::VALUE => $testAttributeValue,
                ProductAttribute::CONTEXT => []
            ]
        ]);
        $this->mockProduct->method('getAttributes')->willReturn($attributeList);

        $this->assertContains($testAttributeValue, $this->productView->getProductPageTitle());
    }

    /**
     * @return array[]
     */
    public function requiredAttributeCodeProvider()
    {
        return [
            ['name'],
            ['product_group'],
            ['brand'],
            ['style'],
        ];
    }

    public function testProductTitleContainsProductTitleSuffix()
    {
        $attributeList = ProductAttributeList::fromArray([]);
        $this->mockProduct->method('getAttributes')->willReturn($attributeList);

        $result = $this->productView->getProductPageTitle();
        $this->assertContains(TwentyOneRunSimpleProductView::PRODUCT_TITLE_SUFFIX, $result);
    }

    public function testProductMetaTitleIsNotExceedingDefinedLimit()
    {
        $maxTitleLength = TwentyOneRunSimpleProductView::MAX_PRODUCT_TITLE_LENGTH;
        $attributeLength = ($maxTitleLength - TwentyOneRunSimpleProductView::PRODUCT_TITLE_SUFFIX) / 2;
        $attributeValue = str_repeat('-', $attributeLength);

        $attributeList = ProductAttributeList::fromArray([
            [
                ProductAttribute::CODE => AttributeCode::fromString('name'),
                ProductAttribute::VALUE => $attributeValue,
                ProductAttribute::CONTEXT => []
            ],
            [
                ProductAttribute::CODE => AttributeCode::fromString('brand'),
                ProductAttribute::VALUE => $attributeValue,
                ProductAttribute::CONTEXT => []
            ],
            [
                ProductAttribute::CODE => AttributeCode::fromString('style'),
                ProductAttribute::VALUE => $attributeValue,
                ProductAttribute::CONTEXT => []
            ],
        ]);

        $this->mockProduct->method('getAttributes')->willReturn($attributeList);

        $this->assertLessThanOrEqual($maxTitleLength, $this->productView->getProductPageTitle());
    }
}
