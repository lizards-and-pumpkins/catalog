<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch\Import;

use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\Import\Product\Composite\AssociatedProductList;
use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\Composite\ProductVariationAttributeList;
use LizardsAndPumpkins\Import\Product\Product;

/**
 * @covers \LizardsAndPumpkins\ProductSearch\Import\ConfigurableProductAttributeValueCollector
 * @uses   \LizardsAndPumpkins\ProductSearch\Import\DefaultAttributeValueCollector
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Import\Product\Composite\AssociatedProductList
 */
class ConfigurableProductAttributeValueCollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigurableProductAttributeValueCollector
     */
    private $valueCollector;

    /**
     * @param string[] $variationAttributes
     * @return ProductVariationAttributeList|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockVariationAttributeList(array $variationAttributes) : ProductVariationAttributeList
    {
        $variationAttributeCodes = array_map(function ($attributeCode) {
            return AttributeCode::fromString($attributeCode);
        }, $variationAttributes);
        $stubVariationAttributeList = $this->createMock(ProductVariationAttributeList::class);
        $stubVariationAttributeList->method('getIterator')->willReturn(new \ArrayIterator($variationAttributeCodes));
        $stubVariationAttributeList->method('getAttributes')->willReturn($variationAttributeCodes);
        return $stubVariationAttributeList;
    }

    /**
     * @param string $variationAttribute
     * @param string[] $values
     * @return Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockProductWithAttributeValues($variationAttribute, array $values) : Product
    {
        $stubAssociatedProduct = $this->createMock(Product::class);
        $stubAssociatedProduct->method('getAllValuesOfAttribute')->with($variationAttribute)->willReturn($values);
        return $stubAssociatedProduct;
    }

    /**
     * @param Product[] $associatedProducts
     * @return AssociatedProductList|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockAssociatedProductList(Product ...$associatedProducts) : AssociatedProductList
    {
        $stubAssociatedProductList = $this->createMock(AssociatedProductList::class);
        $stubAssociatedProductList->method('getIterator')->willReturn(new \ArrayIterator($associatedProducts));
        $stubAssociatedProductList->method('getProducts')->willReturn($associatedProducts);
        return $stubAssociatedProductList;
    }

    protected function setUp()
    {
        $this->valueCollector = new ConfigurableProductAttributeValueCollector();
    }

    public function testItIsASearchableAttributeValueCollector()
    {
        $this->assertInstanceOf(AttributeValueCollector::class, $this->valueCollector);
    }

    public function testItReturnsTheAttributeValuesForConfigurableProducts()
    {
        $stubConfigurableProduct = $this->createMock(ConfigurableProduct::class);
        $stubConfigurableProduct->expects($this->once())
            ->method('getAllValuesOfAttribute')
            ->willReturn(['a value']);

        $stubProductVariationAttributeList = $this->createMock(ProductVariationAttributeList::class);
        $stubConfigurableProduct->method('getVariationAttributes')->willReturn($stubProductVariationAttributeList);

        $result = $this->valueCollector->getValues($stubConfigurableProduct, AttributeCode::fromString('test'));
        $this->assertSame(['a value'], $result);
    }

    public function testItCollectsTheAttributeValuesOfAssociatedProductsForVariationAttributes()
    {
        $variationAttribute = 'test';

        $stubAssociatedProductA = $this->createMockProductWithAttributeValues($variationAttribute, ['value A']);
        $stubAssociatedProductB = $this->createMockProductWithAttributeValues($variationAttribute, ['value B']);

        $stubAssociatedProductList = $this->createMockAssociatedProductList(
            $stubAssociatedProductA,
            $stubAssociatedProductB
        );

        $stubConfigProduct = $this->createMock(ConfigurableProduct::class);
        $stubConfigProduct->expects($this->once())->method('getVariationAttributes')
            ->willReturn($this->createMockVariationAttributeList([$variationAttribute]));
        $stubConfigProduct->method('getAssociatedProducts')->willReturn($stubAssociatedProductList);

        $result = $this->valueCollector->getValues($stubConfigProduct, AttributeCode::fromString($variationAttribute));
        $this->assertSame(['value A', 'value B'], $result);
    }
}
