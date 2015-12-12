<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Product\Product;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\AbstractProductView
 */
class AbstractProductViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StubProductView
     */
    private $productView;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProduct;

    protected function setUp()
    {
        $this->mockProduct = $this->getMock(Product::class);
        $this->productView = new StubProductView($this->mockProduct);
    }

    public function testGettingProductIdIsDelegatedToOriginalProduct()
    {
        $this->mockProduct->expects($this->once())->method('getId');
        $this->productView->getId();
    }

    public function testGettingFirstValueOfProductAttributeIsDelegatedToOriginalProduct()
    {
        $attributeCode = 'foo';
        $this->mockProduct->expects($this->once())->method('getFirstValueOfAttribute')->with($attributeCode);
        $this->productView->getFirstValueOfAttribute($attributeCode);
    }

    public function testGettingAllValuesOfProductAttributeIsDelegatedToOriginalProduct()
    {
        $attributeCode = 'foo';
        $this->mockProduct->expects($this->once())->method('getAllValuesOfAttribute')->with($attributeCode);
        $this->productView->getAllValuesOfAttribute($attributeCode);
    }

    public function testIfProductHasAnAttributeIsDelegatedToOriginalProduct()
    {
        $attributeCode = 'foo';
        $this->mockProduct->expects($this->once())->method('hasAttribute')->with($attributeCode);
        $this->productView->hasAttribute($attributeCode);
    }

    public function testGettingAllAttributesIsDelegatedToOriginalProduct()
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

    public function testGettingProductArrayRepresentationIsDelegatedToOriginalProduct()
    {
        $this->mockProduct->expects($this->once())->method('jsonSerialize');
        $this->productView->jsonSerialize();
    }
}
