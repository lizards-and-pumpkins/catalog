<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductAttribute;
use LizardsAndPumpkins\Product\ProductAttributeList;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\TwentyOneRunSimpleProductView
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Product\ProductAttribute
 * @uses   \LizardsAndPumpkins\Product\ProductAttributeList
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

    protected function setUp()
    {
        $this->mockProduct = $this->getMock(Product::class);
        $this->productView = new TwentyOneRunSimpleProductView($this->mockProduct);
    }

    public function testOriginalProductIsReturned()
    {
        $this->assertSame($this->mockProduct, $this->productView->getOriginalProduct());
    }

    public function testProductViewInterfaceIsImplemented()
    {
        $this->assertInstanceOf(ProductView::class, $this->productView);
    }

    public function testGettingProductIdIsDelegatedToOriginalProduct()
    {
        $this->mockProduct->expects($this->once())->method('getId');
        $this->productView->getId();
    }

    public function testGettingFirstValueOfProductAttributeIsDelegatedToOriginalProduct()
    {
        $testAttributeCode = 'foo';
        $testAttributeValue = 'bar';

        $attribute = new ProductAttribute($testAttributeCode, $testAttributeValue, []);
        $attributeList = new ProductAttributeList($attribute);
        $this->mockProduct->method('getAttributes')->willReturn($attributeList);

        $this->assertSame($testAttributeValue, $this->productView->getFirstValueOfAttribute($testAttributeCode));
    }

    /**
     * @dataProvider priceAttributeCodeProvider
     * @param string $priceAttributeCode
     */
    public function testGettingFirstValueOfPriceAttributeReturnsEmptyString($priceAttributeCode)
    {
        $testAttributeValue = 1000;

        $attribute = new ProductAttribute($priceAttributeCode, $testAttributeValue, []);
        $attributeList = new ProductAttributeList($attribute);
        $this->mockProduct->method('getAttributes')->willReturn($attributeList);

        $this->assertSame('', $this->productView->getFirstValueOfAttribute($priceAttributeCode));
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

    public function testGettingAllValuesOfProductAttributeIsDelegatedToOriginalProduct()
    {
        $testAttributeCode = 'foo';
        $testAttributeValue = 'bar';

        $attribute = new ProductAttribute($testAttributeCode, $testAttributeValue, []);
        $attributeList = new ProductAttributeList($attribute);
        $this->mockProduct->method('getAttributes')->willReturn($attributeList);

        $this->assertSame([$testAttributeValue], $this->productView->getAllValuesOfAttribute($testAttributeCode));
    }

    /**
     * @dataProvider priceAttributeCodeProvider
     * @param string $priceAttributeCode
     */
    public function testGettingAllValuesOfPriceAttributeReturnsEmptyArray($priceAttributeCode)
    {
        $testAttributeValue = 1000;

        $attribute = new ProductAttribute($priceAttributeCode, $testAttributeValue, []);
        $attributeList = new ProductAttributeList($attribute);
        $this->mockProduct->method('getAttributes')->willReturn($attributeList);

        $this->assertSame([], $this->productView->getAllValuesOfAttribute($testAttributeValue));
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

    public function testCheckingIfProductHasAnAttributeIsDelegatedToOriginalProduct()
    {
        $testAttributeCode = 'foo';
        $testAttributeValue = 'bar';

        $attribute = new ProductAttribute($testAttributeCode, $testAttributeValue, []);
        $attributeList = new ProductAttributeList($attribute);
        $this->mockProduct->method('getAttributes')->willReturn($attributeList);

        $this->assertTrue($this->productView->hasAttribute($testAttributeCode));
    }

    /**
     * @dataProvider priceAttributeCodeProvider
     * @param string $priceAttributeCode
     */
    public function testProductViewAttributeListDoesNotHavePrice($priceAttributeCode)
    {
        $testAttributeValue = 1000;

        $attribute = new ProductAttribute($priceAttributeCode, $testAttributeValue, []);
        $attributeList = new ProductAttributeList($attribute);
        $this->mockProduct->method('getAttributes')->willReturn($attributeList);

        $this->assertFalse($this->productView->hasAttribute($priceAttributeCode));
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

    public function testProductAttributeListIsMemoized()
    {
        $attributeList = new ProductAttributeList();
        $this->mockProduct->expects($this->once())->method('getAttributes')->willReturn($attributeList);

        $this->productView->getAttributes();
        $this->productView->getAttributes();
    }

    public function testGettingProductContextIsDelegatedToOriginalProduct()
    {
        $this->mockProduct->expects($this->once())->method('getContext');
        $this->productView->getContext();
    }

    public function testGettingProductImagesIsDelegatedToOriginalProduct()
    {
        $this->mockProduct->expects($this->once())->method('getImages');
        $this->productView->getImages();
    }

    public function testGettingProductImageCountIsDelegatedToOriginalProduct()
    {
        $this->mockProduct->expects($this->once())->method('getImageCount');
        $this->productView->getImageCount();
    }

    public function testGettingProductImageByNumberIsDelegatedToOriginalProduct()
    {
        $testImageNumber = 1;
        $this->mockProduct->expects($this->once())->method('getImageByNumber')->with($testImageNumber);
        $this->productView->getImageByNumber($testImageNumber);
    }

    public function testGettingProductImageFileNameByNumberIsDelegatedToOriginalProduct()
    {
        $testImageNumber = 1;
        $this->mockProduct->expects($this->once())->method('getImageFileNameByNumber')->with($testImageNumber);
        $this->productView->getImageFileNameByNumber($testImageNumber);
    }

    public function testGettingProductImageLabelByNumberIsDelegatedToOriginalProduct()
    {
        $testImageNumber = 1;
        $this->mockProduct->expects($this->once())->method('getImageLabelByNumber')->with($testImageNumber);
        $this->productView->getImageLabelByNumber($testImageNumber);
    }

    public function testGettingProductMainImageFileNameIsDelegatedToOriginalProduct()
    {
        $this->mockProduct->expects($this->once())->method('getMainImageFileName');
        $this->productView->getMainImageFileName();
    }

    public function testGettingProductMainImageLabelIsDelegatedToOriginalProduct()
    {
        $this->mockProduct->expects($this->once())->method('getMainImageLabel');
        $this->productView->getMainImageLabel();
    }

    public function testGettingProductTaxClassIsDelegatedToOriginalProduct()
    {
        $this->mockProduct->expects($this->once())->method('getTaxClass');
        $this->productView->getTaxClass();
    }

    public function testJsonSerializedProductViewHasNoPrices()
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
        $this->mockProduct->method('jsonSerialize')->willReturn([]);

        $result = $this->productView->jsonSerialize();

        /** @var ProductAttributeList $attributesList */
        $attributesList = $result['attributes'];

        $this->assertContains($nonPriceAttribute, $attributesList->getAllAttributes());
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

    public function priceAttributeCodeProvider()
    {
        return [
            ['price'],
            ['special_price']
        ];
    }
}
