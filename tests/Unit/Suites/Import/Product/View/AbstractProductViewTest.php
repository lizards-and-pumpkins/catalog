<?php

namespace LizardsAndPumpkins\Import\Product\View;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Import\Price\PriceSnippetRenderer;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\ProductAttribute;
use LizardsAndPumpkins\Import\Product\ProductAttributeList;
use LizardsAndPumpkins\Import\Product\Image\ProductImage;
use LizardsAndPumpkins\Import\Product\SimpleProduct;
use LizardsAndPumpkins\Import\ImageStorage\Image;
use LizardsAndPumpkins\Import\Product\View\Stub\StubProductView;

/**
 * @covers \LizardsAndPumpkins\Import\Product\View\AbstractProductView
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImage
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttribute
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeList
 */
class AbstractProductViewTest extends \PHPUnit_Framework_TestCase
{
    private $expectedPlaceholderImageLabel = '';

    private $testImageUrl = 'http://example.com/image.jpg';

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

        $stubProductAttributeList = $this->getMock(ProductAttributeList::class);
        $mockProduct->method('getAttributes')->willReturn($stubProductAttributeList);

        $stubImageUrl = $this->getMock(HttpUrl::class, [], [], '', false);
        $stubImageUrl->method('__toString')->willReturn($this->testImageUrl);
        $mockImage = $this->getMock(Image::class);
        $mockImage->method('getUrl')->willReturn($stubImageUrl);
        $mockPlaceholderImage = $this->getMock(Image::class);

        $mockImageFileLocator = $this->getMock(ProductImageFileLocator::class);

        $mockImageFileLocator->method('get')->willReturn($mockImage);
        $mockImageFileLocator->method('getPlaceholder')->willReturn($mockPlaceholderImage);
        $mockImageFileLocator->method('getVariantCodes')->willReturn(['small', 'large']);

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

    public function testGettingTheFirstValueOfAnAttributeUsesTheProcessedAttributeList()
    {
        $attributeCode = 'foo';
        /** @var ProductAttributeList|\PHPUnit_Framework_MockObject_MockObject $mockAttributeList */
        $mockAttributeList = $this->mockProduct->getAttributes();
        $mockAttributeList->expects($this->once())->method('getAllAttributes')->willReturn([
            new ProductAttribute($attributeCode, 'test', []),
        ]);
        $this->assertSame('test', $this->productView->getFirstValueOfAttribute($attributeCode));
    }

    public function testGettingTheFirstValueOfANonExistantAttributeReturnsAnEmptyString()
    {
        /** @var ProductAttributeList|\PHPUnit_Framework_MockObject_MockObject $mockAttributeList */
        $mockAttributeList = $this->mockProduct->getAttributes();
        $mockAttributeList->method('getAllAttributes')->willReturn([]);
        $this->assertSame('', $this->productView->getFirstValueOfAttribute('not_here'));
    }

    /**
     * @dataProvider priceAttributeCodeProvider
     * @param string $priceAttributeCode
     */
    public function testGettingFirstValueOfPriceAttributeReturnsEmptyString($priceAttributeCode)
    {
        $testAttributeValue = 1000;

        $attribute = new ProductAttribute($priceAttributeCode, $testAttributeValue, []);
        /** @var ProductAttributeList|\PHPUnit_Framework_MockObject_MockObject $mockAttributeList */
        $mockAttributeList = $this->mockProduct->getAttributes();
        $mockAttributeList->method('getAllAttributes')->willReturn([$attribute]);

        $this->assertSame('', $this->productView->getFirstValueOfAttribute($priceAttributeCode));
    }

    /**
     * @return array[]
     */
    public function priceAttributeCodeProvider()
    {
        return [
            ['price'],
            ['special_price']
        ];
    }

    public function testGettingAllValuesOfProductAttributeUsesTheProcessedAttributeList()
    {
        $attributeCode = 'foo';
        /** @var ProductAttributeList|\PHPUnit_Framework_MockObject_MockObject $mockAttributeList */
        $mockAttributeList = $this->mockProduct->getAttributes();
        $mockAttributeList->expects($this->once())->method('getAllAttributes')->willReturn([
            new ProductAttribute($attributeCode, 'test1', []),
            new ProductAttribute($attributeCode, 'test2', []),
        ]);
        $this->assertSame(['test1', 'test2'], $this->productView->getAllValuesOfAttribute($attributeCode));
    }

    public function testGettingAllValuesOfANonExistantAttributeReturnsAnEmptyArray()
    {
        /** @var ProductAttributeList|\PHPUnit_Framework_MockObject_MockObject $mockAttributeList */
        $mockAttributeList = $this->mockProduct->getAttributes();
        $mockAttributeList->method('getAllAttributes')->willReturn([]);
        $this->assertSame([], $this->productView->getAllValuesOfAttribute('not_here'));
    }

    public function testHasAttributeMethodUsesTheProcessedAttributeList()
    {
        $attributeCode = 'foo';
        /** @var ProductAttributeList|\PHPUnit_Framework_MockObject_MockObject $mockAttributeList */
        $mockAttributeList = $this->mockProduct->getAttributes();
        $mockAttributeList->expects($this->once())->method('getAllAttributes')->willReturn([
            new ProductAttribute($attributeCode, 'test', []),
        ]);
        $this->assertTrue($this->productView->hasAttribute($attributeCode));
    }

    public function testItRemovesThePriceAndSpecialPriceFromAttributes()
    {
        $priceAttribute = new ProductAttribute(PriceSnippetRenderer::PRICE, 122, []);
        $specialPriceAttribute = new ProductAttribute(PriceSnippetRenderer::SPECIAL_PRICE, 111, []);
        $nonPriceAttribute = new ProductAttribute('not_a_price', 111, []);
        /** @var ProductAttributeList|\PHPUnit_Framework_MockObject_MockObject $stubProductAttributeList */
        $stubProductAttributeList = $this->mockProduct->getAttributes();
        $stubProductAttributeList->method('getAllAttributes')->willReturn([
            $priceAttribute,
            $specialPriceAttribute,
            $nonPriceAttribute,
        ]);

        $result = $this->productView->getAttributes();
        $this->assertNotContains($priceAttribute, $result->getAllAttributes());
        $this->assertNotContains($specialPriceAttribute, $result->getAllAttributes());
        $this->assertContains($nonPriceAttribute, $result->getAllAttributes());
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
        $this->mockProduct->expects($this->once())->method('jsonSerialize')->willReturn([]);
        $this->productView->jsonSerialize();
    }

    public function testItFlattensAttributesInJson()
    {
        $productJsonData = [
            'product_id' => 'test',
            'attributes' => [],
        ];
        $expectedData = [
            'product_id' => 'test',
            'attributes' => [
                'foo' => 'bar',
            ],
        ];
        /** @var ProductAttributeList|\PHPUnit_Framework_MockObject_MockObject $stubProductAttributes */
        $stubProductAttributes = $this->mockProduct->getAttributes();
        $stubProductAttributes->method('getAllAttributes')->willReturn([new ProductAttribute('foo', 'bar', [])]);
        $this->mockProduct->method('getAttributes')->willReturn($stubProductAttributes);
        
        $this->mockProduct->method('jsonSerialize')->willReturn($productJsonData);
        $this->assertSame($expectedData, json_decode(json_encode($this->productView), true));
    }

    public function testItCombinesTheValuesOfAttributesWithTheSameCodeIntoArraysInJson()
    {
        $productJsonData = [
            'product_id' => 'test',
            'attributes' => [],
        ];
        $expectedData = [
            'product_id' => 'test',
            'attributes' => [
                'foo' => ['bar', 'buz', 'qux'],
            ],
        ];
        /** @var ProductAttributeList|\PHPUnit_Framework_MockObject_MockObject $stubProductAttributes */
        $stubProductAttributes = $this->mockProduct->getAttributes();
        $stubProductAttributes->method('getAllAttributes')->willReturn([
            new ProductAttribute('foo', 'bar', []),
            new ProductAttribute('foo', 'buz', []),
            new ProductAttribute('foo', 'qux', []),
        ]);
        $this->mockProduct->method('getAttributes')->willReturn($stubProductAttributes);
        
        $this->mockProduct->method('jsonSerialize')->willReturn($productJsonData);
        $this->assertSame($expectedData, json_decode(json_encode($this->productView), true));
    }

    public function testItRemovesTheContextFromProductJson()
    {
        $productJsonData = [
            'product_id'           => 'test',
            SimpleProduct::CONTEXT => [],
        ];
        $expectedData = [
            'product_id' => 'test',
        ];
        $this->mockProduct->method('jsonSerialize')->willReturn($productJsonData);
        $this->assertSame($expectedData, json_decode(json_encode($this->productView), true));
    }

    public function testItFlattensTheProductImagesInJson()
    {
        $testImageLabel = 'Test Label';
        $mockProductImage = $this->getMock(ProductImage::class, [], [], '', false);
        $mockProductImage->method('getLabel')->willReturn($testImageLabel);
        $this->mockProduct->method('getImages')->willReturn(new \ArrayIterator([$mockProductImage]));

        $productJsonData = [
            'product_id' => 'test',
            'images'     => ['original product image data'],
        ];
        $expectedData = [
            'product_id' => 'test',
            'images'     => [
                'small' => [
                    ['url' => $this->testImageUrl, 'label' => $testImageLabel],
                ],
                'large' => [
                    ['url' => $this->testImageUrl, 'label' => $testImageLabel],
                ],
            ],
        ];
        $this->mockProduct->method('jsonSerialize')->willReturn($productJsonData);
        $this->assertSame($expectedData, json_decode(json_encode($this->productView), true));
    }

    public function testItReturnsPlaceholdersIfTheProductHasNoImages()
    {
        $placeholderUrl = 'http://example.com/placeholder.jpg';
        $stubPlaceholderUrl = $this->getMock(HttpUrl::class, [], [], '', false);
        $stubPlaceholderUrl->method('__toString')->willReturn($placeholderUrl);
        /** @var \PHPUnit_Framework_MockObject_MockObject $placeholderImage */
        $placeholderImage = $this->mockImageFileLocator->getPlaceholder('dummy', $this->mockProduct->getContext());
        $placeholderImage->method('getUrl')->willReturn($stubPlaceholderUrl);

        $this->mockProduct->method('getImages')->willReturn(new \ArrayIterator([]));
        $productJsonData = [
            'product_id' => 'test',
            'images'     => ['original product image data'],
        ];
        $expectedData = [
            'product_id' => 'test',
            'images'     => [
                'small' => [
                    ['url' => $placeholderUrl, 'label' => ''],
                ],
                'large' => [
                    ['url' => $placeholderUrl, 'label' => ''],
                ],
            ],
        ];
        $this->mockProduct->method('jsonSerialize')->willReturn($productJsonData);
        $this->assertSame($expectedData, json_decode(json_encode($this->productView), true));
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

    public function testProductAttributeListIsMemoized()
    {
        /** @var ProductAttributeList|\PHPUnit_Framework_MockObject_MockObject $mockAttributeList */
        $mockAttributeList = $this->mockProduct->getAttributes();
        $mockAttributeList->expects($this->once())->method('getAllAttributes')->willReturn([]);

        $this->productView->getAttributes();
        $this->productView->getAttributes();
    }
}
