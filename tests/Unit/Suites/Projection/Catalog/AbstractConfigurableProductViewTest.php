<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Product\Composite\AssociatedProductList;
use LizardsAndPumpkins\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Product\Composite\ProductVariationAttributeList;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductAttribute;
use LizardsAndPumpkins\Product\ProductAttributeList;
use LizardsAndPumpkins\Product\ProductImage\ProductImageFileLocator;
use LizardsAndPumpkins\Product\SimpleProduct;
use LizardsAndPumpkins\Utils\ImageStorage\Image;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\AbstractConfigurableProductView
 * @uses   \LizardsAndPumpkins\Projection\Catalog\AbstractProductView
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Product\ProductAttribute
 */
class AbstractConfigurableProductViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractConfigurableProductView
     */
    private $configurableProductView;

    /**
     * @var ConfigurableProduct|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProduct;

    private $dummyAssociatedProductViewData = ['dummy product view data'];

    /**
     * @return StubProductView
     */
    private function createConfigurableProductViewInstance()
    {
        /** @var ConfigurableProduct|\PHPUnit_Framework_MockObject_MockObject $mockProduct */
        /** @var ProductImageFileLocator|\PHPUnit_Framework_MockObject_MockObject $mockImageFileLocator */
        /** @var ProductViewLocator|\PHPUnit_Framework_MockObject_MockObject $fakeProductViewLocator */
        $mockProduct = $this->getMock(ConfigurableProduct::class, [], [], '', false);
        $mockProduct->method('getContext')->willReturn($this->getMock(Context::class));

        $mockImage = $this->getMock(Image::class);
        $mockPlaceholderImage = $this->getMock(Image::class);

        $mockImageFileLocator = $this->getMock(ProductImageFileLocator::class);

        $mockImageFileLocator->method('get')->willReturn($mockImage);
        $mockImageFileLocator->method('getPlaceholder')->willReturn($mockPlaceholderImage);
        $mockImageFileLocator->method('getVariantCodes')->willReturn([]);

        $mockImage->method('getUrl')->willReturn($this->getMock(HttpUrl::class, [], [], '', false));

        $mockVariationAttributes = $this->getMock(ProductVariationAttributeList::class, [], [], '', false);
        $mockProduct->method('getVariationAttributes')->willReturn($mockVariationAttributes);

        $mockAssociatedProducts = $this->getMock(AssociatedProductList::class, [], [], '', false);
        $mockProduct->method('getAssociatedProducts')->willReturn($mockAssociatedProducts);

        $fakeProductViewLocator = $this->getMock(ProductViewLocator::class);
        $fakeProductViewLocator->method('createForProduct')->willReturnCallback(function (Product $product) {
            $stubProductView = $product instanceof ConfigurableProduct ?
                $this->getMock(ProductView::class) :
                $this->getMock(CompositeProductView::class);
            $stubProductView->method('jsonSerialize')->willReturn($this->dummyAssociatedProductViewData);
            return $stubProductView;
        });

        return new StubConfigurableProductView(
            $mockProduct,
            $mockImageFileLocator,
            $fakeProductViewLocator
        );
    }

    protected function setUp()
    {
        $this->configurableProductView = $this->createConfigurableProductViewInstance();
        $this->mockProduct = $this->configurableProductView->getOriginalProduct();
    }

    public function testItKeepsVariationAttributes()
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

    public function testItFlattensTheSimpleProductIntoTheMainProduct()
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
        $stubProductAttributes = $this->getMock(ProductAttributeList::class);
        $stubProductAttributes->method('getAllAttributes')->willReturn([new ProductAttribute('foo', 'bar', [])]);
        $this->mockProduct->method('getAttributes')->willReturn($stubProductAttributes);
        $this->mockProduct->method('jsonSerialize')->willReturn($productJsonData);
        $this->assertSame($expectedData, json_decode(json_encode($this->configurableProductView), true));
    }

    public function testAssociatedProductsAreReturnedAsProductViewInstances()
    {
        $stubSimpleProduct = $this->getMock(SimpleProduct::class, [], [], '', false);
        $stubConfigurableProduct = $this->getMock(ConfigurableProduct::class, [], [], '', false);

        /** @var \PHPUnit_Framework_MockObject_MockObject $stubAssociatedProductsList */
        $stubAssociatedProductsList = $this->mockProduct->getAssociatedProducts();
        $stubAssociatedProductsList->method('getProducts')->willReturn([$stubSimpleProduct, $stubConfigurableProduct]);
        $stubAssociatedProductsList->method('getIterator')
            ->willReturn(new \ArrayIterator([$stubSimpleProduct, $stubConfigurableProduct]));

        $this->mockProduct->method('getAssociatedProducts')->willReturn($stubAssociatedProductsList);

        $result = $this->configurableProductView->getAssociatedProducts();
        $this->assertContainsOnlyInstancesOf(ProductView::class, $result);
    }

    public function testItReturnsTheOriginalProductVariationAttributeList()
    {
        $this->mockProduct->expects($this->once())->method('getVariationAttributes');
        $result = $this->configurableProductView->getVariationAttributes();
        $this->assertInstanceOf(ProductVariationAttributeList::class, $result);
    }

    public function testAssociatedProductViewsAreUsedToBuildJsonData()
    {
        $stubChildProduct = $this->getMock(SimpleProduct::class, [], [], '', false);

        /** @var \PHPUnit_Framework_MockObject_MockObject $stubAssociatedProductsList */
        $stubAssociatedProductsList = $this->mockProduct->getAssociatedProducts();
        $stubAssociatedProductsList->method('getProducts')->willReturn([$stubChildProduct]);
        $stubAssociatedProductsList->method('getIterator')
            ->willReturn(new \ArrayIterator([$stubChildProduct]));

        $this->mockProduct->method('jsonSerialize')->willReturn(['associated_products' => [0 => $stubChildProduct]]);
        $this->mockProduct->method('getImages')->willReturn(new \ArrayIterator([]));
        $this->mockProduct->method('getContext')->willReturn($this->getMock(Context::class));

        $result = json_decode(json_encode($this->configurableProductView), true);
        $this->assertSame($this->dummyAssociatedProductViewData, $result[ConfigurableProduct::ASSOCIATED_PRODUCTS][0]);
    }
}
