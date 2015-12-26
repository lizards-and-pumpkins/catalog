<?php

namespace LizardsAndPumpkins\Product\ProductSearch;

use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\Product\Composite\AssociatedProductList;
use LizardsAndPumpkins\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Product\Composite\ProductVariationAttributeList;
use LizardsAndPumpkins\Product\Product;

/**
 * @covers \LizardsAndPumpkins\Product\ProductSearch\ConfigurableProductSearchableAttributeValueCollector
 * @uses   \LizardsAndPumpkins\Product\ProductSearch\DefaultSearchableAttributeValueCollector
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Product\Composite\AssociatedProductList
 */
class ConfigurableProductSearchableAttributeValueCollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigurableProductSearchableAttributeValueCollector
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
        $stubVariationAttributeList = $this->getMock(ProductVariationAttributeList::class, [], [], '', false);
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
        $stubAssociatedProduct = $this->getMock(Product::class);
        $stubAssociatedProduct->method('getAllValuesOfAttribute')->with($variationAttribute)->willReturn($values);
        return $stubAssociatedProduct;
    }

    /**
     * @param Product[] $stubAssociatedProducts
     * @return AssociatedProductList|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockAssociatedProductList(Product ...$associatedProducts)
    {
        $stubAssociatedProductList = $this->getMock(AssociatedProductList::class);
        $stubAssociatedProductList->method('getIterator')->willReturn(new \ArrayIterator($associatedProducts));
        $stubAssociatedProductList->method('getProducts')->willReturn($associatedProducts);
        return $stubAssociatedProductList;
    }

    protected function setUp()
    {
        $this->valueCollector = new ConfigurableProductSearchableAttributeValueCollector();
    }

    public function testItIsASearchableAttributeValueCollector()
    {
        $this->assertInstanceOf(SearchableAttributeValueCollector::class, $this->valueCollector);
    }

    public function testItReturnsTheAttributeValuesForConfigurableProducts()
    {
        $stubConfigurableProduct = $this->getMock(ConfigurableProduct::class, [], [], '', false);
        $stubConfigurableProduct->expects($this->once())
            ->method('getAllValuesOfAttribute')
            ->willReturn(['a value']);
        
        $stubConfigurableProduct->method('getVariationAttributes')->willReturn(new \ArrayIterator([]));

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

        $stubConfigProduct = $this->getMock(ConfigurableProduct::class, [], [], '', false);
        $stubConfigProduct->expects($this->once())->method('getVariationAttributes')
            ->willReturn($this->createMockVariationAttributeList([$variationAttribute]));
        $stubConfigProduct->method('getAssociatedProducts')->willReturn($stubAssociatedProductList);

        $result = $this->valueCollector->getValues($stubConfigProduct, AttributeCode::fromString($variationAttribute));
        $this->assertSame(['value A', 'value B'], $result);
    }
}
