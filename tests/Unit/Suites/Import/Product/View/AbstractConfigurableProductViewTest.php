<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\View;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Import\Product\Composite\AssociatedProductList;
use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\Composite\ProductVariationAttributeList;
use LizardsAndPumpkins\Import\Product\Image\ProductImageList;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\ProductAttribute;
use LizardsAndPumpkins\Import\Product\ProductAttributeList;
use LizardsAndPumpkins\Import\Product\SimpleProduct;
use LizardsAndPumpkins\Import\ImageStorage\Image;
use LizardsAndPumpkins\Import\Product\View\Stub\StubConfigurableProductView;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Product\View\AbstractConfigurableProductView
 * @uses   \LizardsAndPumpkins\Import\Product\View\AbstractProductView
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttribute
 */
class AbstractConfigurableProductViewTest extends TestCase
{
    /**
     * @var AbstractConfigurableProductView
     */
    private $configurableProductView;

    /**
     * @var ConfigurableProduct|MockObject
     */
    private $mockProduct;

    private $dummyAssociatedProductViewData = ['dummy product view data'];

    private function createConfigurableProductViewInstance() : StubConfigurableProductView
    {
        /** @var ConfigurableProduct|MockObject $mockProduct */
        $mockProduct = $this->createMock(ConfigurableProduct::class);
        $mockProduct->method('getContext')->willReturn($this->createMock(Context::class));

        $mockImage = $this->createMock(Image::class);
        $mockPlaceholderImage = $this->createMock(Image::class);

        /** @var ProductImageFileLocator|MockObject $mockImageFileLocator */
        $mockImageFileLocator = $this->createMock(ProductImageFileLocator::class);
        $mockImageFileLocator->method('get')->willReturn($mockImage);
        $mockImageFileLocator->method('getPlaceholder')->willReturn($mockPlaceholderImage);
        $mockImageFileLocator->method('getVariantCodes')->willReturn([]);

        $mockImage->method('getUrl')->willReturn($this->createMock(HttpUrl::class));

        $mockVariationAttributes = $this->createMock(ProductVariationAttributeList::class);
        $mockProduct->method('getVariationAttributes')->willReturn($mockVariationAttributes);

        $mockAssociatedProducts = $this->createMock(AssociatedProductList::class);
        $mockProduct->method('getAssociatedProducts')->willReturn($mockAssociatedProducts);

        /** @var ProductViewLocator|MockObject $fakeProductViewLocator */
        $fakeProductViewLocator = $this->createMock(ProductViewLocator::class);
        $fakeProductViewLocator->method('createForProduct')->willReturnCallback(function (Product $product) {
            $stubProductView = $product instanceof ConfigurableProduct ?
                $this->createMock(ProductView::class) :
                $this->createMock(CompositeProductView::class);
            $stubProductView->method('jsonSerialize')->willReturn($this->dummyAssociatedProductViewData);
            return $stubProductView;
        });

        return new StubConfigurableProductView(
            $mockProduct,
            $mockImageFileLocator,
            $fakeProductViewLocator
        );
    }

    final protected function setUp(): void
    {
        $this->configurableProductView = $this->createConfigurableProductViewInstance();
        $this->mockProduct = $this->configurableProductView->getOriginalProduct();
    }

    public function testItKeepsVariationAttributes(): void
    {
        $productJsonData = [
            'product_id'           => 'test',
            'variation_attributes' => ['foo', 'bar'],
        ];
        $expectedData = [
            'product_id'           => 'test',
            'variation_attributes' => ['foo', 'bar'],
        ];
        $this->mockProduct->method('jsonSerialize')->willReturn($productJsonData);
        $this->assertSame($expectedData, json_decode(json_encode($this->configurableProductView), true));
    }

    public function testItFlattensTheSimpleProductIntoTheMainProduct(): void
    {
        $productJsonData = [
            'simple_product'       => [
                'product_id'           => 'test',
                'attributes'           => [],
                SimpleProduct::CONTEXT => [],
                'images'               => [],
            ],
            'variation_attributes' => ['foo'],
        ];
        $expectedData = [
            'product_id'           => 'test',
            'attributes'           => [
                'foo' => 'bar',
            ],
            'images'               => [],
            'variation_attributes' => ['foo'],
        ];
        $stubProductAttributes = $this->createMock(ProductAttributeList::class);
        $stubProductAttributes->method('getAllAttributes')->willReturn([new ProductAttribute('foo', 'bar', [])]);
        $this->mockProduct->method('getAttributes')->willReturn($stubProductAttributes);
        $this->mockProduct->method('jsonSerialize')->willReturn($productJsonData);
        $this->assertSame($expectedData, json_decode(json_encode($this->configurableProductView), true));
    }

    public function testAssociatedProductsAreReturnedAsProductViewInstances(): void
    {
        $stubSimpleProduct = $this->createMock(SimpleProduct::class);
        $stubConfigurableProduct = $this->createMock(ConfigurableProduct::class);

        /** @var MockObject $stubAssociatedProductsList */
        $stubAssociatedProductsList = $this->mockProduct->getAssociatedProducts();
        $stubAssociatedProductsList->method('getProducts')->willReturn([$stubSimpleProduct, $stubConfigurableProduct]);
        $stubAssociatedProductsList->method('getIterator')
            ->willReturn(new \ArrayIterator([$stubSimpleProduct, $stubConfigurableProduct]));

        $this->mockProduct->method('getAssociatedProducts')->willReturn($stubAssociatedProductsList);

        $result = $this->configurableProductView->getAssociatedProducts();
        $this->assertContainsOnlyInstancesOf(ProductView::class, $result);
    }

    public function testItReturnsTheOriginalProductVariationAttributeList(): void
    {
        $this->mockProduct->expects($this->once())->method('getVariationAttributes');
        $result = $this->configurableProductView->getVariationAttributes();
        $this->assertInstanceOf(ProductVariationAttributeList::class, $result);
    }

    public function testAssociatedProductViewsAreUsedToBuildJsonData(): void
    {
        $stubChildProduct = $this->createMock(SimpleProduct::class);

        /** @var MockObject $stubAssociatedProductsList */
        $stubAssociatedProductsList = $this->mockProduct->getAssociatedProducts();
        $stubAssociatedProductsList->method('getProducts')->willReturn([$stubChildProduct]);
        $stubAssociatedProductsList->method('getIterator')
            ->willReturn(new \ArrayIterator([$stubChildProduct]));

        $this->mockProduct->method('jsonSerialize')->willReturn(['associated_products' => [0 => $stubChildProduct]]);
        $this->mockProduct->method('getImages')->willReturn($this->createMock(ProductImageList::class));
        $this->mockProduct->method('getContext')->willReturn($this->createMock(Context::class));

        $result = json_decode(json_encode($this->configurableProductView), true);
        $this->assertSame($this->dummyAssociatedProductViewData, $result[ConfigurableProduct::ASSOCIATED_PRODUCTS][0]);
    }
}
