<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductAttribute;
use LizardsAndPumpkins\Product\ProductAttributeList;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\TwentyOneRunProductView
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Product\ProductAttribute
 * @uses   \LizardsAndPumpkins\Product\ProductAttributeList
 */
class TwentyOneRunProductViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProduct;

    /**
     * @var TwentyOneRunProductView
     */
    private $productView;

    /**
     * @param $attributeCodeString
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function getStubAttributeCode($attributeCodeString)
    {
        $stubAttributeCode = $this->getMock(AttributeCode::class, [], [], '', false);
        $stubAttributeCode->method('__toString')->willReturn($attributeCodeString);

        return $stubAttributeCode;
    }

    /**
     * @param string $attributeCodeString
     * @return ProductAttribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubAttributeWithCode($attributeCodeString)
    {
        $stubAttributeCode = $this->getStubAttributeCode($attributeCodeString);

        $stubProductAttribute = $this->getMock(ProductAttribute::class, [], [], '', false);
        $stubProductAttribute->method('getCode')->willReturn($stubAttributeCode);
        $stubProductAttribute->method('isCodeEqualTo')->willReturnCallback(function ($code) use ($attributeCodeString) {
            return $code === $attributeCodeString;
        });
        $stubProductAttribute->method('getContextParts')->willReturn([]);
        $stubProductAttribute->method('getContextDataSet')->willReturn([]);

        return $stubProductAttribute;
    }

    /**
     * @param string $attributeCodeString
     * @param mixed $attributeValue
     * @return ProductAttribute|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubAttributeWithCodeAndValue($attributeCodeString, $attributeValue)
    {
        $stubProductAttribute = $this->createStubAttributeWithCode($attributeCodeString);
        $stubProductAttribute->method('getValue')->willReturn($attributeValue);

        return $stubProductAttribute;
    }

    /**
     * @param ProductAttribute[] ...$stubProductAttributes
     * @return ProductAttributeList|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubProductAttributeList(ProductAttribute ...$stubProductAttributes)
    {
        $stubAttributeList = $this->getMock(ProductAttributeList::class, [], [], '', false);
        $stubAttributeList->method('getAllAttributes')->willReturn($stubProductAttributes);

        return $stubAttributeList;
    }

    protected function setUp()
    {
        $this->mockProduct = $this->getMock(Product::class);
        $this->productView = new TwentyOneRunProductView($this->mockProduct);
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

        $stubAttribute = $this->createStubAttributeWithCodeAndValue($testAttributeCode, $testAttributeValue);
        $stubAttributeList = $this->createStubProductAttributeList($stubAttribute);
        $stubAttributeList->method('hasAttribute')->with($testAttributeCode)->willReturn(true);
        $this->mockProduct->method('getAttributes')->willReturn($stubAttributeList);

        $this->assertSame($testAttributeValue, $this->productView->getFirstValueOfAttribute($testAttributeCode));
    }

    public function testGettingFirstValueOfPriceAttributeReturnsEmptyString()
    {
        $testAttributeCode = 'price';
        $testAttributeValue = 1000;

        $stubPriceAttribute = $this->createStubAttributeWithCodeAndValue($testAttributeCode, $testAttributeValue);
        $stubAttributeList = $this->createStubProductAttributeList($stubPriceAttribute);
        $this->mockProduct->method('getAttributes')->willReturn($stubAttributeList);

        $this->assertSame('', $this->productView->getFirstValueOfAttribute($testAttributeCode));
    }

    public function testGettingFirstValueOfSpecialPriceAttributeReturnsEmptyString()
    {
        $testAttributeCode = 'special_price';
        $testAttributeValue = 1000;

        $stubPriceAttribute = $this->createStubAttributeWithCodeAndValue($testAttributeCode, $testAttributeValue);
        $stubAttributeList = $this->createStubProductAttributeList($stubPriceAttribute);
        $this->mockProduct->method('getAttributes')->willReturn($stubAttributeList);

        $this->assertSame('', $this->productView->getFirstValueOfAttribute($testAttributeCode));
    }

    public function testGettingAllValuesOfProductAttributeIsDelegatedToOriginalProduct()
    {
        $testAttributeCode = 'foo';
        $testAttributeValue = 'bar';

        $stubAttribute = $this->createStubAttributeWithCodeAndValue($testAttributeCode, $testAttributeValue);
        $stubAttributeList = $this->createStubProductAttributeList($stubAttribute);
        $stubAttributeList->method('hasAttribute')->with($testAttributeCode)->willReturn(true);
        $this->mockProduct->method('getAttributes')->willReturn($stubAttributeList);

        $this->assertSame([$testAttributeValue], $this->productView->getAllValuesOfAttribute($testAttributeCode));
    }

    public function testGettingAllValuesOfPriceAttributeReturnsEmptyArray()
    {
        $testAttributeCode = 'price';
        $testAttributeValue = 1000;

        $stubAttribute = $this->createStubAttributeWithCodeAndValue($testAttributeCode, $testAttributeValue);
        $stubAttributeList = $this->createStubProductAttributeList($stubAttribute);
        $stubAttributeList->method('hasAttribute')->with($testAttributeCode)->willReturn(true);
        $this->mockProduct->method('getAttributes')->willReturn($stubAttributeList);

        $this->assertSame([], $this->productView->getAllValuesOfAttribute($testAttributeValue));
    }

    public function testGettingAllValuesOfSpecialPriceAttributeReturnsEmptyArray()
    {
        $testAttributeCode = 'special_price';
        $testAttributeValue = 1000;

        $stubAttribute = $this->createStubAttributeWithCodeAndValue($testAttributeCode, $testAttributeValue);
        $stubAttributeList = $this->createStubProductAttributeList($stubAttribute);
        $stubAttributeList->method('hasAttribute')->with($testAttributeCode)->willReturn(true);
        $this->mockProduct->method('getAttributes')->willReturn($stubAttributeList);

        $this->assertSame([], $this->productView->getAllValuesOfAttribute($testAttributeCode));
    }

    public function testCheckingIfProductHasAnAttributeIsDelegatedToOriginalProduct()
    {
        $testAttributeCode = 'foo';

        $stubAttribute = $this->createStubAttributeWithCode($testAttributeCode);
        $stubAttributeList = $this->createStubProductAttributeList($stubAttribute);
        $stubAttributeList->method('hasAttribute')->with($testAttributeCode)->willReturn(true);

        $this->mockProduct->method('getAttributes')->willReturn($stubAttributeList);

        $this->assertTrue($this->productView->hasAttribute($testAttributeCode));
    }

    public function testProductViewAttributeListDoesNotHavePrice()
    {
        $stubPriceAttribute = $this->createStubAttributeWithCode('price');
        $stubAttributeList = $this->createStubProductAttributeList($stubPriceAttribute);
        $stubAttributeList->method('hasAttribute')->with('price')->willReturn(true);

        $this->mockProduct->method('getAttributes')->willReturn($stubAttributeList);

        $this->assertFalse($this->productView->hasAttribute('price'));
    }

    public function testProductViewAttributeListDoesNotHaveSpecialPrice()
    {
        $stubSpecialPriceAttribute = $this->createStubAttributeWithCode('special_price');
        $stubAttributeList = $this->createStubProductAttributeList($stubSpecialPriceAttribute);
        $stubAttributeList->method('hasAttribute')->with('special_price')->willReturn(true);

        $this->mockProduct->method('getAttributes')->willReturn($stubAttributeList);

        $this->assertFalse($this->productView->hasAttribute('special_price'));
    }

    public function testFilteredProductAttributeListIsReturned()
    {
        $nonPriceAttribute = $this->createStubAttributeWithCode('foo');
        $priceAttribute = $this->createStubAttributeWithCode('price');
        $specialPriceAttribute = $this->createStubAttributeWithCode('special_price');

        $stubAttributeList = $this->createStubProductAttributeList(
            $nonPriceAttribute,
            $priceAttribute,
            $specialPriceAttribute
        );

        $this->mockProduct->method('getAttributes')->willReturn($stubAttributeList);

        $result = $this->productView->getAttributes();

        $this->assertCount(1, $result);
        $this->assertContains($nonPriceAttribute, $result->getAllAttributes());
        $this->assertNotContains($priceAttribute, $result->getAllAttributes());
        $this->assertNotContains($specialPriceAttribute, $result->getAllAttributes());
    }

    public function testProductAttributeListIsMemoized()
    {
        $stubAttributeList = $this->createStubProductAttributeList();
        $this->mockProduct->expects($this->once())->method('getAttributes')->willReturn($stubAttributeList);

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
        $nonPriceAttribute = $this->createStubAttributeWithCode('foo');
        $priceAttribute = $this->createStubAttributeWithCode('price');
        $specialPriceAttribute = $this->createStubAttributeWithCode('special_price');

        $stubAttributeList = $this->createStubProductAttributeList(
            $nonPriceAttribute,
            $priceAttribute,
            $specialPriceAttribute
        );

        $this->mockProduct->method('getAttributes')->willReturn($stubAttributeList);
        $this->mockProduct->method('jsonSerialize')->willReturn([]);

        $result = $this->productView->jsonSerialize();

        /** @var ProductAttributeList $attributesList */
        $attributesList = $result['attributes'];

        $this->assertContains($nonPriceAttribute, $attributesList->getAllAttributes());
        $this->assertNotContains($priceAttribute, $attributesList->getAllAttributes());
        $this->assertNotContains($specialPriceAttribute, $attributesList->getAllAttributes());
    }

    public function testMaximumPurchasableQuantityIsReturnedIfProductIsAvailableForBackorders()
    {
        $stockAttributeCode = 'stock_qty';

        $stockQtyAttribute = $this->createStubAttributeWithCodeAndValue($stockAttributeCode, 1);
        $backordersAttribute = $this->createStubAttributeWithCodeAndValue('backorders', 'true');
        $stubAttributeList = $this->createStubProductAttributeList($stockQtyAttribute, $backordersAttribute);

        $this->mockProduct->method('getAttributes')->willReturn($stubAttributeList);
        $this->mockProduct->method('getFirstValueOfAttribute')->with('backorders')->willReturn('true');
        $result = $this->productView->getFirstValueOfAttribute($stockAttributeCode);

        $this->assertSame(TwentyOneRunProductView::MAX_PURCHASABLE_QTY, $result);
    }

    public function testMaximumPurchasableQuantityIsReturnedIfProductQuantityIsGreaterThanMaximumPurchasableQuantity()
    {
        $stockAttributeCode = 'stock_qty';

        $stockQtyAttribute = $this->createStubAttributeWithCodeAndValue($stockAttributeCode, 6);
        $stubAttributeList = $this->createStubProductAttributeList($stockQtyAttribute);

        $this->mockProduct->method('getAttributes')->willReturn($stubAttributeList);
        $result = $this->productView->getFirstValueOfAttribute($stockAttributeCode);

        $this->assertSame(TwentyOneRunProductView::MAX_PURCHASABLE_QTY, $result);
    }
}
