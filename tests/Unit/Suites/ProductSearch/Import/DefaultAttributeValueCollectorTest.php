<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch\Import;

use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\Import\Price\PriceSnippetRenderer;
use LizardsAndPumpkins\Import\Product\Product;

/**
 * @covers \LizardsAndPumpkins\ProductSearch\Import\DefaultAttributeValueCollector
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 */
class DefaultAttributeValueCollectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DefaultAttributeValueCollector
     */
    private $attributeValueCollector;

    /**
     * @var Product|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProduct;

    protected function setUp()
    {
        $this->mockProduct = $this->createMock(Product::class);
        $this->attributeValueCollector = new DefaultAttributeValueCollector();
    }

    public function testItImplementsTheSearchableProductAttributeValueCollectorInterface()
    {
        $this->assertInstanceOf(AttributeValueCollector::class, $this->attributeValueCollector);
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

    /**
     * @dataProvider invalidAttributeValueProvider
     * @param mixed $invalidAttributesValue
     */
    public function testItFiltersInvalidResultValues($invalidAttributesValue)
    {
        $testAttributeCode = AttributeCode::fromString('foo');
        $attributeValues = ['c', 'd', $invalidAttributesValue];

        $this->mockProduct->expects($this->once())
            ->method('getAllValuesOfAttribute')->with($testAttributeCode)
            ->willReturn($attributeValues);
        $result = $this->attributeValueCollector->getValues($this->mockProduct, $testAttributeCode);
        $this->assertSame(['c', 'd'], $result);
    }

    public function invalidAttributeValueProvider() : array
    {
        return [
            ['non-scalar' => ['x', 'y']],
            ['empty-string' => ''],
            ['space-only-string' => '  '],
        ];
    }

    public function testItReturnsTheProductSpecialPriceInsteadOfPriceIfPresent()
    {
        $priceAttributeCode = AttributeCode::fromString(PriceSnippetRenderer::PRICE);
        $specialPriceAttributeCode = AttributeCode::fromString(PriceSnippetRenderer::SPECIAL_PRICE);

        $this->mockProduct->method('hasAttribute')->with($specialPriceAttributeCode)->willReturn(true);

        $this->mockProduct->expects($this->atLeastOnce())
            ->method('getAllValuesOfAttribute')->with($specialPriceAttributeCode)
            ->willReturn([1.99]);
        $this->attributeValueCollector->getValues($this->mockProduct, $priceAttributeCode);
    }

    public function testProductPriceIsReturnedIfSpecialPriceEqualsToEmptyString()
    {
        $testPrice = 2.99;
        $priceAttributeCode = AttributeCode::fromString(PriceSnippetRenderer::PRICE);
        $specialPriceAttributeCode = AttributeCode::fromString(PriceSnippetRenderer::SPECIAL_PRICE);

        $this->mockProduct->method('hasAttribute')->with($specialPriceAttributeCode)->willReturn(true);

        $this->mockProduct->method('getAllValuesOfAttribute')->willReturnMap([
            [PriceSnippetRenderer::PRICE, [$testPrice]],
            [PriceSnippetRenderer::SPECIAL_PRICE, ['']],
        ]);

        $result = $this->attributeValueCollector->getValues($this->mockProduct, $priceAttributeCode);

        $this->assertSame([$testPrice], $result);
    }
}
