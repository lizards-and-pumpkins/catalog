<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\ProductAttribute;
use LizardsAndPumpkins\Product\ProductAttributeList;
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
