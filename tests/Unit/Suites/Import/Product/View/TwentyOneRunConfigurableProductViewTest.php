<?php

namespace LizardsAndPumpkins\Import\Product\View;

use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\ProductAttribute;
use LizardsAndPumpkins\Import\Product\ProductAttributeList;
use LizardsAndPumpkins\Import\Product\View\AbstractConfigurableProductView;
use LizardsAndPumpkins\Import\Product\View\ProductImageFileLocator;
use LizardsAndPumpkins\Import\Product\View\ProductView;
use LizardsAndPumpkins\Import\Product\View\ProductViewLocator;
use LizardsAndPumpkins\Import\Product\View\TwentyOneRunConfigurableProductView;
use LizardsAndPumpkins\ProductDetail\Import\View\TwentyOneRunProductPageTitle;
use LizardsAndPumpkins\Import\ImageStorage\Image;

/**
 * @covers \LizardsAndPumpkins\Import\Product\View\TwentyOneRunConfigurableProductView
 * @uses   \LizardsAndPumpkins\Import\Product\View\AbstractProductView
 * @uses   \LizardsAndPumpkins\Import\Product\View\AbstractConfigurableProductView
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Import\Product\Composite\AssociatedProductList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttribute
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeList
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
     * @var TwentyOneRunProductPageTitle|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubPageTitle;

    /**
     * @var ProductViewLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductViewLocator;

    /**
     * @var ProductImageFileLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductImageFileLocator;
    
    protected function setUp()
    {
        $this->stubProductViewLocator = $this->getMock(ProductViewLocator::class);
        $this->mockProduct = $this->getMock(ConfigurableProduct::class, [], [], '', false);
        $this->stubPageTitle = $this->getMock(TwentyOneRunProductPageTitle::class, [], [], '', false);
        $this->stubProductImageFileLocator = $this->getMock(ProductImageFileLocator::class);
        $this->stubProductImageFileLocator->method('getPlaceholder')->willReturn($this->getMock(Image::class));
        $this->stubProductImageFileLocator->method('getVariantCodes')->willReturn(['large']);

        $this->productView = new TwentyOneRunConfigurableProductView(
            $this->stubProductViewLocator,
            $this->mockProduct,
            $this->stubPageTitle,
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

    public function testProductPageTitleCreationIsDelegatedToPageTitle()
    {
        $testTitle = 'foo';
        $this->stubPageTitle->method('forProductView')->willReturn($testTitle);

        $this->assertSame($testTitle, $this->productView->getProductPageTitle());
    }
}
