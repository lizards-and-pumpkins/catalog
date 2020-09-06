<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\View;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Import\Price\PriceSnippetRenderer;
use LizardsAndPumpkins\Import\Product\Image\ProductImageList;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\ProductAttribute;
use LizardsAndPumpkins\Import\Product\ProductAttributeList;
use LizardsAndPumpkins\Import\Product\Image\ProductImage;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\Import\Product\SimpleProduct;
use LizardsAndPumpkins\Import\ImageStorage\Image;
use LizardsAndPumpkins\Import\Product\View\Stub\StubProductView;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Product\View\AbstractProductView
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImage
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttribute
 * @uses   \LizardsAndPumpkins\Import\Product\ProductId
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeList
 */
class AbstractProductViewTest extends TestCase
{
    private $expectedPlaceholderImageLabel = '';

    private $testImageUrl = 'http://example.com/image.jpg';

    /**
     * @var StubProductView
     */
    private $productView;

    /**
     * @var Product|MockObject
     */
    private $mockProduct;

    /**
     * @var ProductImageFileLocator
     */
    private $mockImageFileLocator;

    private function createProductViewInstance() : StubProductView
    {
        $mockProduct = $this->createMock(Product::class);
        $mockProduct->method('getContext')->willReturn($this->createMock(Context::class));

        $stubProductAttributeList = $this->createMock(ProductAttributeList::class);
        $mockProduct->method('getAttributes')->willReturn($stubProductAttributeList);

        $stubImageUrl = $this->createMock(HttpUrl::class);
        $stubImageUrl->method('__toString')->willReturn($this->testImageUrl);
        $mockImage = $this->createMock(Image::class);
        $mockImage->method('getUrl')->willReturn($stubImageUrl);
        $mockPlaceholderImage = $this->createMock(Image::class);

        $mockImageFileLocator = $this->createMock(ProductImageFileLocator::class);

        $mockImageFileLocator->method('get')->willReturn($mockImage);
        $mockImageFileLocator->method('getPlaceholder')->willReturn($mockPlaceholderImage);
        $mockImageFileLocator->method('getVariantCodes')->willReturn(['small', 'large']);

        $mockImage->method('getUrl')->willReturn($this->createMock(HttpUrl::class));

        return new StubProductView($mockProduct, $mockImageFileLocator);
    }

    final protected function setUp(): void
    {
        $this->productView = $this->createProductViewInstance();
        $this->mockProduct = $this->productView->getOriginalProduct();
        $this->mockImageFileLocator = $this->productView->imageFileLocator;
    }

    public function testItIsJsonSerializable(): void
    {
        $this->assertInstanceOf(\JsonSerializable::class, $this->productView);
    }

    public function testGettingProductIdIsDelegatedToOriginalProduct(): void
    {
        $this->mockProduct->expects($this->once())->method('getId')->willReturn(new ProductId('foo'));
        $this->productView->getId();
    }

    public function testGettingTheFirstValueOfAnAttributeUsesTheProcessedAttributeList(): void
    {
        $attributeCode = 'foo';
        /** @var ProductAttributeList|MockObject $mockAttributeList */
        $mockAttributeList = $this->mockProduct->getAttributes();
        $mockAttributeList->expects($this->once())->method('getAllAttributes')->willReturn([
            new ProductAttribute($attributeCode, 'test', []),
        ]);
        $this->assertSame('test', $this->productView->getFirstValueOfAttribute($attributeCode));
    }

    public function testCastsTheFirstValueOfAttributesToString(): void
    {
        $attributeCode = 'foo';
        $integerAttributeValue = 1;
        /** @var ProductAttributeList|MockObject $mockAttributeList */
        $mockAttributeList = $this->mockProduct->getAttributes();
        $mockAttributeList->expects($this->once())->method('getAllAttributes')->willReturn([
            new ProductAttribute($attributeCode, $integerAttributeValue, []),
        ]);
        $this->assertSame("$integerAttributeValue", $this->productView->getFirstValueOfAttribute($attributeCode));
    }

    public function testGettingTheFirstValueOfANonExistentAttributeReturnsAnEmptyString(): void
    {
        /** @var ProductAttributeList|MockObject $mockAttributeList */
        $mockAttributeList = $this->mockProduct->getAttributes();
        $mockAttributeList->method('getAllAttributes')->willReturn([]);
        $this->assertSame('', $this->productView->getFirstValueOfAttribute('not_here'));
    }

    /**
     * @dataProvider priceAttributeCodeProvider
     * @param string $priceAttributeCode
     */
    public function testGettingFirstValueOfPriceAttributeReturnsEmptyString(string $priceAttributeCode): void
    {
        $testAttributeValue = 1000;

        $attribute = new ProductAttribute($priceAttributeCode, $testAttributeValue, []);
        /** @var ProductAttributeList|MockObject $mockAttributeList */
        $mockAttributeList = $this->mockProduct->getAttributes();
        $mockAttributeList->method('getAllAttributes')->willReturn([$attribute]);

        $this->assertSame('', $this->productView->getFirstValueOfAttribute($priceAttributeCode));
    }

    /**
     * @return array[]
     */
    public function priceAttributeCodeProvider() : array
    {
        return [
            ['price'],
            ['special_price']
        ];
    }

    public function testGettingAllValuesOfProductAttributeUsesTheProcessedAttributeList(): void
    {
        $attributeCode = 'foo';
        /** @var ProductAttributeList|MockObject $mockAttributeList */
        $mockAttributeList = $this->mockProduct->getAttributes();
        $mockAttributeList->expects($this->once())->method('getAllAttributes')->willReturn([
            new ProductAttribute($attributeCode, 'test1', []),
            new ProductAttribute($attributeCode, 'test2', []),
        ]);
        $this->assertSame(['test1', 'test2'], $this->productView->getAllValuesOfAttribute($attributeCode));
    }

    public function testGettingAllValuesOfANonExistentAttributeReturnsAnEmptyArray(): void
    {
        /** @var ProductAttributeList|MockObject $mockAttributeList */
        $mockAttributeList = $this->mockProduct->getAttributes();
        $mockAttributeList->method('getAllAttributes')->willReturn([]);
        $this->assertSame([], $this->productView->getAllValuesOfAttribute('not_here'));
    }

    public function testHasAttributeMethodUsesTheProcessedAttributeList(): void
    {
        $attributeCode = 'foo';
        /** @var ProductAttributeList|MockObject $mockAttributeList */
        $mockAttributeList = $this->mockProduct->getAttributes();
        $mockAttributeList->expects($this->once())->method('getAllAttributes')->willReturn([
            new ProductAttribute($attributeCode, 'test', []),
        ]);
        $this->assertTrue($this->productView->hasAttribute($attributeCode));
    }

    public function testItRemovesThePriceAndSpecialPriceFromAttributes(): void
    {
        $priceAttribute = new ProductAttribute(PriceSnippetRenderer::PRICE, 122, []);
        $specialPriceAttribute = new ProductAttribute(PriceSnippetRenderer::SPECIAL_PRICE, 111, []);
        $nonPriceAttribute = new ProductAttribute('not_a_price', 111, []);
        /** @var ProductAttributeList|MockObject $stubProductAttributeList */
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

    public function testGettingProductContextIsDelegatedToOriginalProduct(): void
    {
        $this->mockProduct->expects($this->once())->method('getContext');
        $this->productView->getContext();
    }

    public function testGettingProductImagesIsDelegatedToOriginalProduct(): void
    {
        $variantCode = 'medium';

        $stubProductImage = $this->createMock(ProductImage::class);

        $stubProductImageList = $this->createMock(ProductImageList::class);
        $stubProductImageList->method('getIterator')->willReturn(new \ArrayIterator([$stubProductImage]));

        $this->mockProduct->method('getImages')->willReturn($stubProductImageList);

        $result = $this->productView->getImages($variantCode);
        
        $this->assertIsArray($result);
        $this->assertContainsOnlyInstancesOf(Image::class, $result);
        $this->assertCount(1, $result);
    }

    public function testGettingProductImageCountIsDelegatedToOriginalProduct(): void
    {
        $this->mockProduct->expects($this->once())->method('getImageCount');
        $this->productView->getImageCount();
    }

    public function testGettingProductImageByNumberIsDelegatedToOriginalProduct(): void
    {
        $testImageNumber = 1;
        $variantCode = 'medium';
        $this->mockProduct->method('getImageCount')->willReturn(1);
        $this->mockProduct->expects($this->once())
            ->method('getImageByNumber')->with($testImageNumber)
            ->willReturn($this->createMock(ProductImage::class));
        $this->productView->getImageByNumber($testImageNumber, $variantCode);
    }

    public function testGettingProductImageUrlByNumberReturnsAHttpUrlInstance(): void
    {
        $testImageNumber = 1;
        $variantCode = 'medium';
        $this->mockProduct->method('getImageCount')->willReturn(1);
        $this->mockProduct->expects($this->once())
            ->method('getImageByNumber')->with($testImageNumber)
            ->willReturn($this->createMock(ProductImage::class));
        $result = $this->productView->getImageUrlByNumber($testImageNumber, $variantCode);
        $this->assertInstanceOf(HttpUrl::class, $result);
    }

    public function testGettingProductImageLabelByNumberIsDelegatedToOriginalProduct(): void
    {
        $testImageNumber = 1;
        $this->mockProduct->method('getImageCount')->willReturn(1);
        $this->mockProduct->expects($this->once())->method('getImageLabelByNumber')->with($testImageNumber);
        $this->productView->getImageLabelByNumber($testImageNumber);
    }

    public function testGettingProductMainImageUrlReturnsAHttpUrlInstance(): void
    {
        $variantCode = 'medium';
        $this->mockProduct->method('getImageCount')->willReturn(1);
        $this->mockProduct->expects($this->once())
            ->method('getImageByNumber')
            ->willReturn($this->createMock(ProductImage::class));
        $result = $this->productView->getMainImageUrl($variantCode);
        $this->assertInstanceOf(HttpUrl::class, $result);
    }

    public function testGettingProductMainImageLabelIsDelegatedToOriginalProduct(): void
    {
        $this->mockProduct->method('getImageCount')->willReturn(1);
        $this->mockProduct->expects($this->once())->method('getMainImageLabel');
        $this->productView->getMainImageLabel();
    }

    public function testGettingProductArrayRepresentationIsDelegatedToOriginalProduct(): void
    {
        $this->mockProduct->expects($this->once())->method('jsonSerialize')->willReturn([]);
        $this->productView->jsonSerialize();
    }

    public function testItFlattensAttributesInJson(): void
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
        /** @var ProductAttributeList|MockObject $stubProductAttributes */
        $stubProductAttributes = $this->mockProduct->getAttributes();
        $stubProductAttributes->method('getAllAttributes')->willReturn([new ProductAttribute('foo', 'bar', [])]);
        $this->mockProduct->method('getAttributes')->willReturn($stubProductAttributes);
        
        $this->mockProduct->method('jsonSerialize')->willReturn($productJsonData);
        $this->assertSame($expectedData, json_decode(json_encode($this->productView), true));
    }

    public function testItCombinesTheValuesOfAttributesWithTheSameCodeIntoArraysInJson(): void
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
        /** @var ProductAttributeList|MockObject $stubProductAttributes */
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

    public function testItRemovesTheContextFromProductJson(): void
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

    public function testItFlattensTheProductImagesInJson(): void
    {
        $testImageLabel = 'Test Label';

        $stubProductImage = $this->createMock(ProductImage::class);
        $stubProductImage->method('getLabel')->willReturn($testImageLabel);

        $stubProductImageList = $this->createMock(ProductImageList::class);
        $stubProductImageList->method('getIterator')->willReturn(new \ArrayIterator([$stubProductImage]));

        $this->mockProduct->method('getImages')->willReturn($stubProductImageList);

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

    public function testItReturnsPlaceholdersIfTheProductHasNoImages(): void
    {
        $placeholderUrl = 'http://example.com/placeholder.jpg';
        $stubPlaceholderUrl = $this->createMock(HttpUrl::class);
        $stubPlaceholderUrl->method('__toString')->willReturn($placeholderUrl);
        /** @var MockObject $placeholderImage */
        $placeholderImage = $this->mockImageFileLocator->getPlaceholder('dummy', $this->mockProduct->getContext());
        $placeholderImage->method('getUrl')->willReturn($stubPlaceholderUrl);

        $stubProductImageList = $this->createMock(ProductImageList::class);

        $this->mockProduct->method('getImages')->willReturn($stubProductImageList);
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

    public function testGettingAnImageByNumberHigherThenTheImageCountWillReturnThePlaceholderImage(): void
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

    public function testGettingAnImageUrlByNumberHigherThenTheImageCountWillReturnThePlaceholderImageUrl(): void
    {
        $testImageNumber = 2;
        $this->mockProduct->method('getImageCount')->willReturn(1);

        $variantCode = 'medium';
        $stubHttpUrl = $this->createMock(HttpUrl::class);
        /** @var MockObject $placeholderImage */
        $placeholderImage = $this->mockImageFileLocator->getPlaceholder($variantCode, $this->mockProduct->getContext());
        $placeholderImage->expects($this->once())->method('getUrl')->willReturn($stubHttpUrl);

        $this->assertSame($stubHttpUrl, $this->productView->getImageUrlByNumber($testImageNumber, $variantCode));
    }

    public function testGettingAnImageLabelByNumberHigherThenTheImageCountWillReturnAnEmptyString(): void
    {
        $testImageNumber = 2;
        $this->mockProduct->method('getImageCount')->willReturn(1);

        $imageLabel = $this->productView->getImageLabelByNumber($testImageNumber);

        $this->assertSame($this->expectedPlaceholderImageLabel, $imageLabel);
    }

    public function testGettingTheMainImageUrlFromAProductWithoutImagesWillReturnThePlaceholderImageUrl(): void
    {
        $this->mockProduct->method('getImageCount')->willReturn(0);

        $variantCode = 'medium';
        $stubHttpUrl = $this->createMock(HttpUrl::class);
        /** @var MockObject $placeholderImage */
        $placeholderImage = $this->mockImageFileLocator->getPlaceholder($variantCode, $this->mockProduct->getContext());
        $placeholderImage->expects($this->once())->method('getUrl')->willReturn($stubHttpUrl);

        $result = $this->productView->getMainImageUrl($variantCode);

        $this->assertSame($stubHttpUrl, $result);
    }

    public function testGettingTheMainImageLabelFromAProductWithoutImagesWillReturnAnEmptyString(): void
    {
        $this->mockProduct->method('getImageCount')->willReturn(0);

        $imageLabel = $this->productView->getMainImageLabel();

        $this->assertSame($this->expectedPlaceholderImageLabel, $imageLabel);
    }

    public function testProductAttributeListIsMemoized(): void
    {
        /** @var ProductAttributeList|MockObject $mockAttributeList */
        $mockAttributeList = $this->mockProduct->getAttributes();
        $mockAttributeList->expects($this->once())->method('getAllAttributes')->willReturn([]);

        $this->productView->getAttributes();
        $this->productView->getAttributes();
    }
}
