<?php

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
    private function createMockVariationAttributeList(array $variationAttributes)
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockProductWithAttributeValues($variationAttribute, array $values)
    {
        $stubAssociatedProduct = $this->createMock(Product::class);
        $stubAssociatedProduct->method('getAllValuesOfAttribute')->with($variationAttribute)->willReturn($values);
        return $stubAssociatedProduct;
    }

    /**
     * @param Product[] $associatedProducts
     * @return AssociatedProductList|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockAssociatedProductList(Product ...$associatedProducts)
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
        /** @var ConfigurableProduct|\PHPUnit_Framework_MockObject_MockObject $stubConfigurableProduct */
        $stubConfigurableProduct = $this->createMock(ConfigurableProduct::class);
        $stubConfigurableProduct->expects($this->once())
            ->method('getAllValuesOfAttribute')
            ->willReturn(['a value']);
        
        $stubConfigurableProduct->method('getVariationAttributes')->willReturn(new \ArrayIterator([]));

        $result = $this->valueCollector->getValues($stubConfigurableProduct, AttributeCode::fromString('test'));
        $this->assertSame(['a value'], $result);
    }

    public function testItCollectsTheAttributeValuesOfAssociatedProductsForVariationAttributes()
    {
        $variationAttributeCode = 'test';

        $stubAssociatedProductA = $this->createMockProductWithAttributeValues($variationAttributeCode, ['value A']);
        $stubAssociatedProductB = $this->createMockProductWithAttributeValues($variationAttributeCode, ['value B']);

        $stubAssociatedProductList = $this->createMockAssociatedProductList(
            $stubAssociatedProductA,
            $stubAssociatedProductB
        );

        /** @var ConfigurableProduct|\PHPUnit_Framework_MockObject_MockObject $stubConfigurableProduct */
        $stubConfigurableProduct = $this->createMock(ConfigurableProduct::class);
        $stubConfigurableProduct->expects($this->once())->method('getVariationAttributes')
            ->willReturn($this->createMockVariationAttributeList([$variationAttributeCode]));
        $stubConfigurableProduct->method('getSalableAssociatedProducts')->willReturn($stubAssociatedProductList);

        $attributeCode = AttributeCode::fromString($variationAttributeCode);
        
        $result = $this->valueCollector->getValues($stubConfigurableProduct, $attributeCode);
        $this->assertSame(['value A', 'value B'], $result);
    }
}
