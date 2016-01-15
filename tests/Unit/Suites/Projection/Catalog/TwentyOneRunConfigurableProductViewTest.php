<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Product\CompositeProduct;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductAttribute;
use LizardsAndPumpkins\Product\ProductAttributeList;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Product\ProductImage\ProductImageFileLocator;
use LizardsAndPumpkins\Product\SimpleProduct;
use LizardsAndPumpkins\Utils\ImageStorage\Image;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\TwentyOneRunConfigurableProductView
 * @uses   \LizardsAndPumpkins\Projection\Catalog\AbstractProductView
 * @uses   \LizardsAndPumpkins\Projection\Catalog\AbstractConfigurableProductView
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Product\Composite\AssociatedProductList
 * @uses   \LizardsAndPumpkins\Product\ProductAttribute
 * @uses   \LizardsAndPumpkins\Product\ProductAttributeList
 */
class TwentyOneRunConfigurableProductViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigurableProduct|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProduct;

    /**
     * @var TwentyOneRunConfigurableProductView
     */
    private $productView;

    /**
     * @var ProductViewLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductViewLocator;

    /**
     * @var ProductImageFileLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductImageFileLocator;
    
    private $dummyAssociatedProductData = ['dummy product data'];

    /**
     * @param string $productIdString
     * @return SimpleProduct|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSimpleProductWithId($productIdString)
    {
        $stubSimpleProductId = $this->getMock(ProductId::class, [], [], '', false);
        $stubSimpleProductId->method('__toString')->willReturn($productIdString);

        $stubSimpleProduct = $this->getMock(SimpleProduct::class, [], [], '', false);
        $stubSimpleProduct->method('getId')->willReturn($stubSimpleProductId);

        return $stubSimpleProduct;
    }

    /**
     * @return ProductViewLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubProductViewLocator()
    {
        $stubProductViewLocator = $this->getMock(ProductViewLocator::class);
        $stubProductViewLocator->method('createForProduct')->willReturnCallback(function (Product $product) {
            $stubProductViewType = $product instanceof CompositeProduct ?
                CompositeProductView::class :
                ProductView::class;
            $stubProductView = $this->getMock($stubProductViewType);
            $stubProductView->method('getId')->willReturn($product->getId());
            $stubProductView->method('jsonSerialize')->willReturn($this->dummyAssociatedProductData);
            return $stubProductView;
        });

        return $stubProductViewLocator;
    }

    protected function setUp()
    {
        $this->stubProductViewLocator = $this->createStubProductViewLocator();
        $this->mockProduct = $this->getMock(ConfigurableProduct::class, [], [], '', false);
        $this->stubProductImageFileLocator = $this->getMock(ProductImageFileLocator::class);
        $this->stubProductImageFileLocator->method('getPlaceholder')->willReturn($this->getMock(Image::class));
        $this->stubProductImageFileLocator->method('getVariantCodes')->willReturn(['large']);

        $this->productView = new TwentyOneRunConfigurableProductView(
            $this->stubProductViewLocator,
            $this->mockProduct,
            $this->stubProductImageFileLocator
        );
    }

    public function testProductViewInterfaceIsImplemented()
    {
        $this->assertInstanceOf(ProductView::class, $this->productView);
    }

    public function testItExtendsTheAbstractConfigurableProductView()
    {
        $this->assertInstanceOf(AbstractConfigurableProductView::class, $this->productView);
    }

    public function testOriginalProductIsReturned()
    {
        $this->assertSame($this->mockProduct, $this->productView->getOriginalProduct());
    }

    public function testGettingFirstValueOfBackordersAttributeReturnsEmptyString()
    {
        $testAttributeCode = 'backorders';
        $testAttributeValue = 'true';

        $attribute = new ProductAttribute($testAttributeCode, $testAttributeValue, []);
        $attributeList = new ProductAttributeList($attribute);
        $this->mockProduct->method('getAttributes')->willReturn($attributeList);

        $this->assertSame('', $this->productView->getFirstValueOfAttribute($testAttributeCode));
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
}
