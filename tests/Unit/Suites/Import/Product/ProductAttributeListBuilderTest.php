<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Product\ProductAttribute;
use LizardsAndPumpkins\Import\Product\ProductAttributeList;
use LizardsAndPumpkins\Import\Product\Exception\ProductAttributeContextPartsMismatchException;
use LizardsAndPumpkins\Import\Product\ProductAttributeListBuilder;

/**
 * @covers \LizardsAndPumpkins\Import\Product\ProductAttributeListBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttribute
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 */
class ProductAttributeListBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param mixed[] $contextDataSet
     * @return Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubContextWithDataSet(array $contextDataSet)
    {
        $context = $this->getMock(Context::class);
        $context->method('matchesDataSet')->with($contextDataSet)->willReturn(true);

        return $context;
    }

    public function testAttributeListBuilderIsCreatedFromAttributesArray()
    {
        $contextDataSet = [];

        $attributeData = [
            ProductAttribute::CODE => 'foo',
            ProductAttribute::CONTEXT => $contextDataSet,
            ProductAttribute::VALUE => 'bar'
        ];

        $attributeListBuilder = ProductAttributeListBuilder::fromArray([$attributeData]);
        $context = $this->createStubContextWithDataSet($contextDataSet);

        $result = $attributeListBuilder->getAttributeListForContext($context);
        $expectedProductAttributeList = new ProductAttributeList(ProductAttribute::fromArray($attributeData));

        $this->assertEquals($expectedProductAttributeList, $result);
    }

    public function testItMayContainMultipleProductAttributesWithTheSameCode()
    {
        $contextDataSet = [];

        $attributeDataA = [
            ProductAttribute::CODE    => 'foo',
            ProductAttribute::CONTEXT => $contextDataSet,
            ProductAttribute::VALUE   => 'bar'
        ];
        $attributeDataB = [
            ProductAttribute::CODE    => 'foo',
            ProductAttribute::CONTEXT => $contextDataSet,
            ProductAttribute::VALUE   => 'baz'
        ];

        $attributeListBuilder = ProductAttributeListBuilder::fromArray([$attributeDataA, $attributeDataB]);
        $context = $this->createStubContextWithDataSet($contextDataSet);

        $result = $attributeListBuilder->getAttributeListForContext($context);
        $expectedProductAttributeList = new ProductAttributeList(
            ProductAttribute::fromArray($attributeDataA),
            ProductAttribute::fromArray($attributeDataB)
        );

        $this->assertEquals($expectedProductAttributeList, $result);
    }

    public function testExceptionIsThrownWhenCombiningAttributesWithSameCodeButDifferentContextPartsIntoList()
    {
        $attributeA = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'attribute_code1',
            ProductAttribute::CONTEXT => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
            ProductAttribute::VALUE => 'A'
        ]);
        $attributeB = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'attribute_code2',
            ProductAttribute::CONTEXT => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
            ProductAttribute::VALUE => 'B'
        ]);
        $attributeC = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'attribute_code2',
            ProductAttribute::CONTEXT => [
                'foo' => 'bar',
            ],
            ProductAttribute::VALUE => 'C'
        ]);

        $this->expectException(ProductAttributeContextPartsMismatchException::class);
        $this->expectExceptionMessage(
            'The attribute "attribute_code2" has multiple values with different contexts ' .
            'which can not be part of one product attribute list'
        );
        new ProductAttributeListBuilder($attributeA, $attributeB, $attributeC);
    }
}
