<?php

namespace LizardsAndPumpkins\ProductSearch\Import;

use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\Import\Product\Composite\AssociatedProductList;
use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\Composite\ProductVariationAttributeList;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\ProductSearch\Import\AttributeValueCollector;
use LizardsAndPumpkins\ProductSearch\Import\ConfigurableProductAttributeValueCollector;

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
     * @param Product[] $associatedProducts
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
        $this->valueCollector = new ConfigurableProductAttributeValueCollector();
    }

    public function testItIsASearchableAttributeValueCollector()
    {
        $this->assertInstanceOf(AttributeValueCollector::class, $this->valueCollector);
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
