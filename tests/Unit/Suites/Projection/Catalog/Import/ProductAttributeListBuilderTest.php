<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\ProductAttribute;
use LizardsAndPumpkins\Projection\Catalog\Import\Exception\ProductAttributeContextPartsMismatchException;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\Import\ProductAttributeListBuilder
 * @uses   \LizardsAndPumpkins\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Product\ProductAttribute
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 */
class ProductAttributeListBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param ProductAttributeListBuilder $attributeListBuilder
     * @return ProductAttribute[]
     */
    public function getAttributesArrayFromBuilderInstance(ProductAttributeListBuilder $attributeListBuilder)
    {
        $property = new \ReflectionProperty($attributeListBuilder, 'attributes');
        $property->setAccessible(true);
        return $property->getValue($attributeListBuilder);
    }

    /**
     * @param ProductAttributeListBuilder $attributeListBuilder
     * @param string $code
     * @return ProductAttribute[]
     */
    private function getAttributesByCodeFromInstance(ProductAttributeListBuilder $attributeListBuilder, $code)
    {
        $attributes = $this->getAttributesArrayFromBuilderInstance($attributeListBuilder);
        return array_values(array_filter($attributes, function (ProductAttribute $attribute) use ($code) {
            return $attribute->isCodeEqualTo($code);
        }));
    }
    
    public function testAttributeListBuilderIsCreatedFromAttributesArray()
    {
        $attributeArray = [
            [
                'code' => 'foo',
                'contextData' => [],
                'value' => 'bar'
            ]
        ];

        $attributeList = ProductAttributeListBuilder::fromArray($attributeArray);
        $attributesWithCode = $this->getAttributesByCodeFromInstance($attributeList, 'foo');
        $attributeWithCode = $attributesWithCode[0];

        $this->assertEquals('bar', $attributeWithCode->getValue());
    }

    public function testItMayContainMultipleProductAttributesWithTheSameCode()
    {
        $attributeArray = [
            ['code' => 'foo', 'contextData' => [], 'value' => 'bar'],
            ['code' => 'foo', 'contextData' => [], 'value' => 'baz'],
        ];

        $attributeList = ProductAttributeListBuilder::fromArray($attributeArray);
        $result = $this->getAttributesByCodeFromInstance($attributeList, 'foo');

        $this->assertCount(count($attributeArray), $result);
        $this->assertContainsOnly(ProductAttribute::class, $result);
    }

    public function testExceptionIsThrownWhenCombiningAttributesWithSameCodeButDifferentContextPartsIntoList()
    {
        $attributeA = ProductAttribute::fromArray([
            'code' => 'attribute_code1',
            'contextData' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
            'value' => 'A'
        ]);
        $attributeB = ProductAttribute::fromArray([
            'code' => 'attribute_code2',
            'contextData' => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
            'value' => 'B'
        ]);
        $attributeC = ProductAttribute::fromArray([
            'code' => 'attribute_code2',
            'contextData' => [
                'foo' => 'bar',
            ],
            'value' => 'C'
        ]);

        $this->setExpectedException(
            ProductAttributeContextPartsMismatchException::class,
            'The attribute "attribute_code2" has multiple values with different contexts ' .
            'which can not be part of one product attribute list'
        );
        new ProductAttributeListBuilder($attributeA, $attributeB, $attributeC);
    }

    public function testAttributeValuesForAGivenContextAreExtracted()
    {
        $contextDataA = ['website' => 'A'];
        $contextDataB = ['website' => 'B'];
        $attributesArray = [
            ['code' => 'foo', 'contextData' => $contextDataA, 'value' => 'expected'],
            ['code' => 'foo', 'contextData' => $contextDataA, 'value' => 'expected'],
            ['code' => 'bar', 'contextData' => $contextDataA, 'value' => 'expected'],
            ['code' => 'foo', 'contextData' => $contextDataB, 'value' => 'not-expected'],
            ['code' => 'buz', 'contextData' => $contextDataB, 'value' => 'not-expected'],
        ];

        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);
        $stubContext->method('matchesDataSet')->willReturnMap([
            [$contextDataA, true],
            [$contextDataB, false],
        ]);
        $originalAttributeList = ProductAttributeListBuilder::fromArray($attributesArray);
        $matchingAttributeList = $originalAttributeList->getAttributeListForContext($stubContext);
        $this->assertCount(3, $matchingAttributeList);
    }

    /**
     * @return array[]
     */
    public function numberOfAttributesToAddProvider()
    {
        return [
            [0, []],
            [1, ['attr_1']],
            [2, ['attr_1', 'attr_2']],
        ];
    }
}
