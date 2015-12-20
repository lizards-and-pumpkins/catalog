<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder\ContextLocale;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductImage\ProductImage;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\AbstractProductView
 * @uses   \LizardsAndPumpkins\Product\ProductImage\ProductImage
 * @uses   \LizardsAndPumpkins\Product\ProductAttribute
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Product\ProductAttributeList
 */
class AbstractProductViewTest extends \PHPUnit_Framework_TestCase
{
    private $expectedPlaceholderImageFile = 'placeholder/placeholder-image-de_DE.jpg';

    private $expectedPlaceholderImageLabel = '';

    /**
     * @var StubProductView
     */
    private $productView;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProduct;

    /**
     * @param string $localeCode
     * @return StubProductView
     */
    private function createProductViewInstanceWithLocaleCode($localeCode)
    {
        $stubContext = $this->getMock(Context::class);
        $stubContext->method('getValue')->with(ContextLocale::CODE)->willReturn($localeCode);
        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $mockProduct */
        $mockProduct = $this->getMock(Product::class);
        $mockProduct->method('getContext')->willReturn($stubContext);
        return new StubProductView($mockProduct);
    }

    protected function setUp()
    {
        $localeCode = 'de_DE';
        $this->productView = $this->createProductViewInstanceWithLocaleCode($localeCode);
        $this->mockProduct = $this->productView->getOriginalProduct();
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
        $this->mockProduct->method('getImageCount')->willReturn(1);
        $this->mockProduct->expects($this->once())->method('getImageByNumber')->with($testImageNumber);
        $this->productView->getImageByNumber($testImageNumber);
    }

    public function testGettingProductImageFileNameByNumberIsDelegatedToOriginalProduct()
    {
        $testImageNumber = 1;
        $this->mockProduct->method('getImageCount')->willReturn(1);
        $this->mockProduct->expects($this->once())->method('getImageFileNameByNumber')->with($testImageNumber);
        $this->productView->getImageFileNameByNumber($testImageNumber);
    }

    public function testGettingProductImageLabelByNumberIsDelegatedToOriginalProduct()
    {
        $testImageNumber = 1;
        $this->mockProduct->method('getImageCount')->willReturn(1);
        $this->mockProduct->expects($this->once())->method('getImageLabelByNumber')->with($testImageNumber);
        $this->productView->getImageLabelByNumber($testImageNumber);
    }

    public function testGettingProductMainImageFileNameIsDelegatedToOriginalProduct()
    {
        $this->mockProduct->method('getImageCount')->willReturn(1);
        $this->mockProduct->expects($this->once())->method('getMainImageFileName');
        $this->productView->getMainImageFileName();
    }

    public function testGettingProductMainImageLabelIsDelegatedToOriginalProduct()
    {
        $this->mockProduct->method('getImageCount')->willReturn(1);
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

    public function testGettingAnImageByNumberHigherThenTheImageCountWillReturnThePlaceholderImage()
    {
        $testImageNumber = 2;
        $this->mockProduct->method('getImageCount')->willReturn(1);

        $image = $this->productView->getImageByNumber($testImageNumber);

        $this->assertInstanceOf(ProductImage::class, $image);
        $this->assertSame($this->expectedPlaceholderImageFile, $image->getFileName());
        $this->assertSame($this->expectedPlaceholderImageLabel, $image->getLabel());
    }

    public function testGettingAnImageFileNameByNumberHigherThenTheImageCountWillReturnThePlaceholderImageFileName()
    {
        $testImageNumber = 2;
        $this->mockProduct->method('getImageCount')->willReturn(1);

        $imageFileName = $this->productView->getImageFileNameByNumber($testImageNumber);

        $this->assertSame($this->expectedPlaceholderImageFile, $imageFileName);
    }

    public function testGettingAnImageLabelByNumberHigherThenTheImageCountWillReturnAnEmptyString()
    {
        $testImageNumber = 2;
        $this->mockProduct->method('getImageCount')->willReturn(1);

        $imageLabel = $this->productView->getImageLabelByNumber($testImageNumber);

        $this->assertSame($this->expectedPlaceholderImageLabel, $imageLabel);
    }

    public function testGettingTheMainImageFileNameFromAProductWithoutImagesWillReturnThePlaceholderImageFileName()
    {
        $this->mockProduct->method('getImageCount')->willReturn(0);

        $imageFileName = $this->productView->getMainImageFileName();

        $this->assertSame($this->expectedPlaceholderImageFile, $imageFileName);
    }

    public function testGettingTheMainImageLabelFromAProductWithoutImagesWillReturnAnEmptyString()
    {
        $this->mockProduct->method('getImageCount')->willReturn(0);

        $imageLabel = $this->productView->getMainImageLabel();

        $this->assertSame($this->expectedPlaceholderImageLabel, $imageLabel);
    }

    /**
     * @dataProvider localeCodeDataProvider
     */
    public function testThePlaceholderImageFileNameContainsTheWebsiteCodeAsASuffix($localeCode)
    {
        $productView = $this->createProductViewInstanceWithLocaleCode($localeCode);
        $productView->getOriginalProduct()->method('getImageCount')->willReturn(0);

        $placeholderImageName = $productView->getMainImageFileName();

        $this->assertRegExp("#-{$localeCode}\\.(jpe?g|png|svg)$#", $placeholderImageName);
    }

    /**
     * @return array[]
     */
    public function localeCodeDataProvider()
    {
        return [
            ['de_DE'],
            ['fr_FR'],
        ];
    }
}
