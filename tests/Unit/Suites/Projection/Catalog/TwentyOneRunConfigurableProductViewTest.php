<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Product\Composite\AssociatedProductList;
use LizardsAndPumpkins\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Product\CompositeProduct;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductAttribute;
use LizardsAndPumpkins\Product\ProductAttributeList;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Product\SimpleProduct;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\TwentyOneRunConfigurableProductView
 * @uses   \LizardsAndPumpkins\Projection\Catalog\AbstractProductView
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
     * @param string $productIdString
     * @return ConfigurableProduct|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubConfigurableProductWithId($productIdString)
    {
        $stubConfigurableProductId = $this->getMock(ProductId::class, [], [], '', false);
        $stubConfigurableProductId->method('__toString')->willReturn($productIdString);

        $stubConfigurableProduct = $this->getMock(ConfigurableProduct::class, [], [], '', false);
        $stubConfigurableProduct->method('getId')->willReturn($stubConfigurableProductId);

        return $stubConfigurableProduct;
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
            return $stubProductView;
        });

        return $stubProductViewLocator;
    }

    protected function setUp()
    {
        $this->stubProductViewLocator = $this->createStubProductViewLocator();
        $this->mockProduct = $this->getMock(ConfigurableProduct::class, [], [], '', false);

        $this->productView = new TwentyOneRunConfigurableProductView($this->stubProductViewLocator, $this->mockProduct);
    }

    public function testProductViewInterfaceIsImplemented()
    {
        $this->assertInstanceOf(ProductView::class, $this->productView);
    }

    public function testOriginalProductIsReturned()
    {
        $this->assertSame($this->mockProduct, $this->productView->getOriginalProduct());
    }

    public function testGettingFirstValueOfProductAttributeIsDelegatedToOriginalProduct()
    {
        $testAttributeCode = 'foo';
        $testAttributeValue = 'bar';

        $attribute = new ProductAttribute($testAttributeCode, $testAttributeValue, []);
        $attributeList = new ProductAttributeList($attribute);
        $this->mockProduct->method('getAttributes')->willReturn($attributeList);

        $this->assertSame($testAttributeValue, $this->productView->getFirstValueOfAttribute($testAttributeCode));
    }

    /**
     * @dataProvider priceAttributeCodeProvider
     * @param string $priceAttributeCode
     */
    public function testGettingFirstValueOfPriceAttributeReturnsEmptyString($priceAttributeCode)
    {
        $testAttributeValue = 1000;

        $attribute = new ProductAttribute($priceAttributeCode, $testAttributeValue, []);
        $attributeList = new ProductAttributeList($attribute);
        $this->mockProduct->method('getAttributes')->willReturn($attributeList);

        $this->assertSame('', $this->productView->getFirstValueOfAttribute($priceAttributeCode));
    }

    public function testGettingFirstValueOfBackordersAttributeReturnsEmptyString()
    {
        $testAttributeCode = 'backorders';
        $testAttributeValue = true;

        $attribute = new ProductAttribute($testAttributeCode, $testAttributeValue, []);
        $attributeList = new ProductAttributeList($attribute);
        $this->mockProduct->method('getAttributes')->willReturn($attributeList);

        $this->assertSame('', $this->productView->getFirstValueOfAttribute($testAttributeCode));
    }

    public function testGettingAllValuesOfProductAttributeIsDelegatedToOriginalProduct()
    {
        $testAttributeCode = 'foo';
        $testAttributeValue = 'bar';

        $attribute = new ProductAttribute($testAttributeCode, $testAttributeValue, []);
        $attributeList = new ProductAttributeList($attribute);
        $this->mockProduct->method('getAttributes')->willReturn($attributeList);

        $this->assertSame([$testAttributeValue], $this->productView->getAllValuesOfAttribute($testAttributeCode));
    }

    /**
     * @dataProvider priceAttributeCodeProvider
     * @param string $priceAttributeCode
     */
    public function testGettingAllValuesOfPriceAttributeReturnsEmptyArray($priceAttributeCode)
    {
        $testAttributeValue = 1000;

        $attribute = new ProductAttribute($priceAttributeCode, $testAttributeValue, []);
        $attributeList = new ProductAttributeList($attribute);
        $this->mockProduct->method('getAttributes')->willReturn($attributeList);

        $this->assertSame([], $this->productView->getAllValuesOfAttribute($testAttributeValue));
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

    public function testCheckingIfProductHasAnAttributeIsDelegatedToOriginalProduct()
    {
        $testAttributeCode = 'foo';
        $testAttributeValue = 'bar';

        $attribute = new ProductAttribute($testAttributeCode, $testAttributeValue, []);
        $attributeList = new ProductAttributeList($attribute);
        $this->mockProduct->method('getAttributes')->willReturn($attributeList);

        $this->assertTrue($this->productView->hasAttribute($testAttributeCode));
    }

    /**
     * @dataProvider priceAttributeCodeProvider
     * @param string $priceAttributeCode
     */
    public function testProductViewAttributeListDoesNotHavePrice($priceAttributeCode)
    {
        $testAttributeValue = 1000;

        $attribute = new ProductAttribute($priceAttributeCode, $testAttributeValue, []);
        $attributeList = new ProductAttributeList($attribute);
        $this->mockProduct->method('getAttributes')->willReturn($attributeList);

        $this->assertFalse($this->productView->hasAttribute($priceAttributeCode));
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

    public function testProductAttributeListIsMemoized()
    {
        $attributeList = new ProductAttributeList();
        $this->mockProduct->expects($this->once())->method('getAttributes')->willReturn($attributeList);

        $this->productView->getAttributes();
        $this->productView->getAttributes();
    }

    public function testJsonSerializedProductViewHasNoPrices()
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
        $this->mockProduct->method('jsonSerialize')->willReturn([]);

        $result = $this->productView->jsonSerialize();

        /** @var ProductAttributeList $attributesList */
        $attributesList = $result['attributes'];

        $this->assertContains($nonPriceAttribute, $attributesList->getAllAttributes());
    }

    public function testGettingVariationAttributesIsDelegatedToOriginalProduct()
    {
        $this->mockProduct->expects($this->once())->method('getVariationAttributes');
        $this->productView->getVariationAttributes();
    }

    public function testAssociatedProductsAreReturnedAsProductViewInstances()
    {
        $stubSimpleProduct = $this->createStubSimpleProductWithId('foo');
        $stubConfigurableProduct = $this->createStubConfigurableProductWithId('bar');

        $stubAssociatedProductsList = $this->getMock(AssociatedProductList::class, [], [], '', false);
        $stubAssociatedProductsList->method('getProducts')->willReturn([$stubSimpleProduct, $stubConfigurableProduct]);

        $this->mockProduct->method('getAssociatedProducts')->willReturn($stubAssociatedProductsList);

        $result = $this->productView->getAssociatedProducts();
        $this->assertContainsOnlyInstancesOf(ProductView::class, $result);
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
}
