<?php

namespace LizardsAndPumpkins\Product\ProductSearch;

use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\Product\PriceSnippetRenderer;
use LizardsAndPumpkins\Product\Product;

/**
 * @covers \LizardsAndPumpkins\Product\ProductSearch\DefaultSearchableAttributeValueCollector
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 */
class DefaultSearchableAttributeValueCollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DefaultSearchableAttributeValueCollector
     */
    private $attributeValueCollector;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProduct;

    protected function setUp()
    {
        $this->mockProduct = $this->getMock(Product::class);
        $this->attributeValueCollector = new DefaultSearchableAttributeValueCollector();
    }
    
    public function testItImplementsTheSearchableProductAttributeValueCollectorInterface()
    {
        $this->assertInstanceOf(SearchableAttributeValueCollector::class, $this->attributeValueCollector);
    }

    public function testItReturnsTheProductAttributeValues()
    {
        $testAttributeCode = AttributeCode::fromString('foo');
        $attributeValues = ['a', 'b', 'c'];
        $this->mockProduct->expects($this->once())
            ->method('getAllValuesOfAttribute')->with($testAttributeCode)
            ->willReturn($attributeValues);
        $result = $this->attributeValueCollector->getValues($this->mockProduct, $testAttributeCode);
        $this->assertSame($attributeValues, $result);
    }

    public function testItFiltersNonScalarResultValues()
    {
        $testAttributeCode = AttributeCode::fromString('foo');
        $attributeValues = ['c', 'd', ['x', 'y']];

        $this->mockProduct->expects($this->once())
            ->method('getAllValuesOfAttribute')->with($testAttributeCode)
            ->willReturn($attributeValues);
        $result = $this->attributeValueCollector->getValues($this->mockProduct, $testAttributeCode);
        $this->assertSame(['c', 'd'], $result);
    }

    public function testItReturnsTheProductSpecialPriceInsteadOfPriceIfPresent()
    {
        $priceAttributeCode = AttributeCode::fromString(PriceSnippetRenderer::PRICE);
        $specialPriceAttributeCode = AttributeCode::fromString(PriceSnippetRenderer::SPECIAL_PRICE);

        $this->mockProduct->method('hasAttribute')->with($specialPriceAttributeCode)->willReturn(true);
        
        $this->mockProduct->expects($this->once())
            ->method('getAllValuesOfAttribute')->with($specialPriceAttributeCode)
            ->willReturn([1.99]);
        $this->attributeValueCollector->getValues($this->mockProduct, $priceAttributeCode);
    }
}
