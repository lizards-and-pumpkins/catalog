<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Product\Product;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\IntegrationTestProductView
 */
class IntegrationTestProductViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProduct;

    /**
     * @var IntegrationTestProductView
     */
    private $productView;

    protected function setUp()
    {
        $this->mockProduct = $this->getMock(Product::class);
        $this->productView = new IntegrationTestProductView($this->mockProduct);
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
        $this->mockProduct->expects($this->once())->method('getFirstValueOfAttribute')->with($testAttributeCode);
        $this->productView->getFirstValueOfAttribute($testAttributeCode);
    }

    public function testGettingAllValuesOfProductAttributeIsDelegatedToOriginalProduct()
    {
        $testAttributeCode = 'foo';
        $this->mockProduct->expects($this->once())->method('getAllValuesOfAttribute')->with($testAttributeCode);
        $this->productView->getAllValuesOfAttribute($testAttributeCode);
    }

    public function testCheckingIfProductHasAnAttributeIsDelegatedToOriginalProduct()
    {
        $testAttributeCode = 'foo';
        $this->mockProduct->expects($this->once())->method('hasAttribute')->with($testAttributeCode);
        $this->productView->hasAttribute($testAttributeCode);
    }

    public function testGettingAlProductAttributesIsDelegatedToOriginalProduct()
    {
        $this->mockProduct->expects($this->once())->method('getAttributes');
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

    public function testGettingJsonSerializableProductIsDelegatedToOriginalProduct()
    {
        $this->mockProduct->expects($this->once())->method('jsonSerialize');
        $this->productView->jsonSerialize();
    }
}
