<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductImage\ProductImage;
use LizardsAndPumpkins\Product\ProductImage\ProductImageFileLocator;
use LizardsAndPumpkins\Utils\ImageStorage\Image;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\AbstractProductView
 * @uses   \LizardsAndPumpkins\Product\ProductImage\ProductImage
 * @uses   \LizardsAndPumpkins\Product\ProductAttribute
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Product\ProductAttributeList
 */
class AbstractProductViewTest extends \PHPUnit_Framework_TestCase
{
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
     * @var ProductImageFileLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockImageFileLocator;

    /**
     * @return StubProductView
     */
    private function createProductViewInstance()
    {
        /** @var Product|\PHPUnit_Framework_MockObject_MockObject $mockProduct */
        /** @var ProductImageFileLocator|\PHPUnit_Framework_MockObject_MockObject $mockImageFileLocator */
        $mockProduct = $this->getMock(Product::class);
        $mockProduct->method('getContext')->willReturn($this->getMock(Context::class));

        $mockImage = $this->getMock(Image::class);
        $mockPlaceholderImage = $this->getMock(Image::class);
        
        $mockImageFileLocator = $this->getMock(ProductImageFileLocator::class);
        
        $mockImageFileLocator->method('get')->willReturn($mockImage);
        $mockImageFileLocator->method('getPlaceholder')->willReturn($mockPlaceholderImage);
        
        $mockImage->method('getUrl')->willReturn($this->getMock(HttpUrl::class, [], [], '', false));
        
        return new StubProductView($mockProduct, $mockImageFileLocator);
    }

    protected function setUp()
    {
        $this->productView = $this->createProductViewInstance();
        $this->mockProduct = $this->productView->getOriginalProduct();
        $this->mockImageFileLocator = $this->productView->imageFileLocator;
    }

    public function testItIsJsonSerializable()
    {
        $this->assertInstanceOf(\JsonSerializable::class, $this->productView);
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
        $variantCode = 'medium';
        $stubProductImage = $this->getMock(ProductImage::class, [], [], '', false);
        $this->mockProduct->method('getImages')->willReturn(new \ArrayIterator([$stubProductImage]));
        $result = $this->productView->getImages($variantCode);
        $this->assertInternalType('array', $result);
        $this->assertContainsOnlyInstancesOf(Image::class, $result);
        $this->assertCount(1, $result);
    }

    public function testGettingProductImageCountIsDelegatedToOriginalProduct()
    {
        $this->mockProduct->expects($this->once())->method('getImageCount');
        $this->productView->getImageCount();
    }

    public function testGettingProductImageByNumberIsDelegatedToOriginalProduct()
    {
        $testImageNumber = 1;
        $variantCode = 'medium';
        $this->mockProduct->method('getImageCount')->willReturn(1);
        $this->mockProduct->expects($this->once())
            ->method('getImageByNumber')->with($testImageNumber)
            ->willReturn($this->getMock(ProductImage::class, [], [], '', false));
        $this->productView->getImageByNumber($testImageNumber, $variantCode);
    }

    public function testGettingProductImageUrlByNumberReturnsAHttpUrlInstance()
    {
        $testImageNumber = 1;
        $variantCode = 'medium';
        $this->mockProduct->method('getImageCount')->willReturn(1);
        $this->mockProduct->expects($this->once())
            ->method('getImageByNumber')->with($testImageNumber)
            ->willReturn($this->getMock(ProductImage::class, [], [], '', false));
        $result = $this->productView->getImageUrlByNumber($testImageNumber, $variantCode);
        $this->assertInstanceOf(HttpUrl::class, $result);
    }

    public function testGettingProductImageLabelByNumberIsDelegatedToOriginalProduct()
    {
        $testImageNumber = 1;
        $this->mockProduct->method('getImageCount')->willReturn(1);
        $this->mockProduct->expects($this->once())->method('getImageLabelByNumber')->with($testImageNumber);
        $this->productView->getImageLabelByNumber($testImageNumber);
    }

    public function testGettingProductMainImageUrlReturnsAHttpUrlInstance()
    {
        $variantCode = 'medium';
        $this->mockProduct->method('getImageCount')->willReturn(1);
        $this->mockProduct->expects($this->once())
            ->method('getImageByNumber')
            ->willReturn($this->getMock(ProductImage::class, [], [], '', false));
        $result = $this->productView->getMainImageUrl($variantCode);
        $this->assertInstanceOf(HttpUrl::class, $result);
    }

    public function testGettingProductMainImageLabelIsDelegatedToOriginalProduct()
    {
        $this->mockProduct->method('getImageCount')->willReturn(1);
        $this->mockProduct->expects($this->once())->method('getMainImageLabel');
        $this->productView->getMainImageLabel();
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

        $variantCode = 'small';
        $expectedPlaceholderImage = $this->productView->imageFileLocator->getPlaceholder(
            $variantCode,
            $this->mockProduct->getContext()
        );

        $image = $this->productView->getImageByNumber($testImageNumber, $variantCode);

        $this->assertSame($expectedPlaceholderImage, $image);
        
    }

    public function testGettingAnImageUrlByNumberHigherThenTheImageCountWillReturnThePlaceholderImageUrl()
    {
        $testImageNumber = 2;
        $this->mockProduct->method('getImageCount')->willReturn(1);

        $variantCode = 'medium';
        $stubHttpUrl = $this->getMock(HttpUrl::class, [], [], '', false);
        /** @var \PHPUnit_Framework_MockObject_MockObject $placeholderImage */
        $placeholderImage = $this->mockImageFileLocator->getPlaceholder($variantCode, $this->mockProduct->getContext());
        $placeholderImage->expects($this->once())->method('getUrl')->willReturn($stubHttpUrl);
        
        $this->assertSame($stubHttpUrl, $this->productView->getImageUrlByNumber($testImageNumber, $variantCode));
    }

    public function testGettingAnImageLabelByNumberHigherThenTheImageCountWillReturnAnEmptyString()
    {
        $testImageNumber = 2;
        $this->mockProduct->method('getImageCount')->willReturn(1);

        $imageLabel = $this->productView->getImageLabelByNumber($testImageNumber);

        $this->assertSame($this->expectedPlaceholderImageLabel, $imageLabel);
    }

    public function testGettingTheMainImageUrlFromAProductWithoutImagesWillReturnThePlaceholderImageUrl()
    {
        $this->mockProduct->method('getImageCount')->willReturn(0);

        $variantCode = 'medium';
        $stubHttpUrl = $this->getMock(HttpUrl::class, [], [], '', false);
        /** @var \PHPUnit_Framework_MockObject_MockObject $placeholderImage */
        $placeholderImage = $this->mockImageFileLocator->getPlaceholder($variantCode, $this->mockProduct->getContext());
        $placeholderImage->expects($this->once())->method('getUrl')->willReturn($stubHttpUrl);

        $result = $this->productView->getMainImageUrl($variantCode);
        
        $this->assertSame($stubHttpUrl, $result);
    }

    public function testGettingTheMainImageLabelFromAProductWithoutImagesWillReturnAnEmptyString()
    {
        $this->mockProduct->method('getImageCount')->willReturn(0);

        $imageLabel = $this->productView->getMainImageLabel();

        $this->assertSame($this->expectedPlaceholderImageLabel, $imageLabel);
    }
}
