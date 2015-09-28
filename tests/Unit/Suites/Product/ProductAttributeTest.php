<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Product\Exception\InvalidProductAttributeValueException;
use LizardsAndPumpkins\Product\Exception\ProductAttributeDoesNotContainContextPartException;

/**
 * @covers \LizardsAndPumpkins\Product\ProductAttribute
 * @uses   \LizardsAndPumpkins\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 */
class ProductAttributeTest extends \PHPUnit_Framework_TestCase
{
    public function testTrueIsReturnedIfAttributeHasGivenCode()
    {
        $attribute = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'foo',
            ProductAttribute::CONTEXT_DATA => [],
            ProductAttribute::VALUE => ProductAttribute::VALUE
        ]);

        $this->assertTrue($attribute->isCodeEqualTo('foo'));
    }

    public function testFalseIsReturnedIfAttributeHasDifferentCode()
    {
        $attribute = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'foo',
            ProductAttribute::CONTEXT_DATA => [],
            ProductAttribute::VALUE => ProductAttribute::VALUE
        ]);

        $this->assertFalse($attribute->isCodeEqualTo('bar'));
    }

    public function testAttributeCodeIsReturned()
    {
        $attribute = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'foo',
            ProductAttribute::CONTEXT_DATA => [],
            ProductAttribute::VALUE => 'bar'
        ]);

        $this->assertEquals('foo', (string)$attribute->getCode());
    }

    public function testItReturnsAnAttributeCodeInstance()
    {
        $attribute = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'test_code',
            ProductAttribute::CONTEXT_DATA => [],
            ProductAttribute::VALUE => 'test-value'
        ]);

        $this->assertInstanceOf(AttributeCode::class, $attribute->getCode());
    }

    public function testAttributeValueIsReturned()
    {
        $attribute = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'foo',
            ProductAttribute::CONTEXT_DATA => [],
            ProductAttribute::VALUE => 'bar'
        ]);

        $this->assertEquals('bar', $attribute->getValue());
    }

    public function testItThrowsAnExceptionIfAttributeIsNotAScalar()
    {
        $this->setExpectedException(
            InvalidProductAttributeValueException::class,
            'The product attribute "foo" has to have a scalar value, got "array"'
        );
        ProductAttribute::fromArray([
            ProductAttribute::CODE => 'foo',
            ProductAttribute::CONTEXT_DATA => [],
            ProductAttribute::VALUE => [
                [
                    ProductAttribute::CODE => 'bar',
                    ProductAttribute::CONTEXT_DATA => [],
                    ProductAttribute::VALUE => 1
                ],
            ]
        ]);
    }

    public function testContextPartsOfAttributeAreReturned()
    {
        $contextData = ['foo' => 'bar', 'baz' => 'qux'];

        $attribute = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'attribute_a_code',
            ProductAttribute::CONTEXT_DATA => $contextData,
            ProductAttribute::VALUE => 'attributeAValue'
        ]);

        $this->assertSame(array_keys($contextData), $attribute->getContextParts());
    }

    public function testExceptionIsThrownIfRequestedContextPartIsNotPresent()
    {
        $this->setExpectedException(
            ProductAttributeDoesNotContainContextPartException::class,
            'The context part "foo" is not present on the attribute "attribute_code"'
        );
        $attribute = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'attribute_code',
            ProductAttribute::CONTEXT_DATA => [],
            ProductAttribute::VALUE => 'attributeValue'
        ]);
        $attribute->getContextPartValue('foo');
    }

    public function testItReturnsTheContextPartIfItIsPresent()
    {
        $attribute = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'attribute_code',
            ProductAttribute::CONTEXT_DATA => ['foo' => 'bar'],
            ProductAttribute::VALUE => 'attributeValue'
        ]);
        $this->assertSame('bar', $attribute->getContextPartValue('foo'));
    }

    public function testFalseIsReturnedIfContentPartsOfAttributesAreDifferent()
    {
        $attributeA = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'attribute_a_code',
            ProductAttribute::CONTEXT_DATA => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
            ProductAttribute::VALUE => 'attributeAValue'
        ]);
        $attributeB = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'attribute_b_code',
            ProductAttribute::CONTEXT_DATA => [
                'foo' => 'bar',
            ],
            ProductAttribute::VALUE => 'attributeBValue'
        ]);

        $this->assertFalse($attributeA->hasSameContextPartsAs($attributeB));
    }

    public function testTrueIsReturnedIfContentPartsOfAttributesAreIdentical()
    {
        $attributeA = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'attribute_a_code',
            ProductAttribute::CONTEXT_DATA => [
                'foo' => 'bar',
                'baz' => 'qux',
            ],
            ProductAttribute::VALUE => 'attributeAValue'
        ]);
        $attributeB = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'attribute_b_code',
            ProductAttribute::CONTEXT_DATA => [
                'foo' => 'qux',
                'baz' => 'bar'
            ],
            ProductAttribute::VALUE => 'attributeBValue'
        ]);

        $this->assertTrue($attributeA->hasSameContextPartsAs($attributeB));
    }

    public function testFalseIsReturnedIfAttributeCodesAreDifferent()
    {
        $attributeA = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'code_a',
            ProductAttribute::CONTEXT_DATA => [],
            ProductAttribute::VALUE => 'valueA'
        ]);
        $attributeB = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'code_b',
            ProductAttribute::CONTEXT_DATA => [],
            ProductAttribute::VALUE => 'valueB'
        ]);

        $this->assertFalse($attributeA->isCodeEqualTo($attributeB));
    }

    public function testTrueIsReturnedIfAttributeCodesAreIdentical()
    {
        $attributeA = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'code_a',
            ProductAttribute::CONTEXT_DATA => [],
            ProductAttribute::VALUE => 'valueA'
        ]);
        $attributeB = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'code_a',
            ProductAttribute::CONTEXT_DATA => [],
            ProductAttribute::VALUE => 'valueB'
        ]);

        $this->assertTrue($attributeA->isCodeEqualTo($attributeB));
    }

    public function testItReturnsTheContextDataSet()
    {
        $contextDataSet = [
            'foo' => 'bar',
            'buz' => 'qux'
        ];
        $attribute = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'test',
            ProductAttribute::CONTEXT_DATA => $contextDataSet,
            ProductAttribute::VALUE => 'abc'
        ]);
        $this->assertSame($contextDataSet, $attribute->getContextDataSet());
    }

    public function testItIsSerializable()
    {
        $attribute = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'test',
            ProductAttribute::CONTEXT_DATA => [],
            ProductAttribute::VALUE => 'abc'
        ]);
        $this->assertInstanceOf(\JsonSerializable::class, $attribute);
    }

    public function testItCanBeSerializedAndRehydrated()
    {
        $sourceAttribute = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'test',
            ProductAttribute::CONTEXT_DATA => ['foo' => 'bar'],
            ProductAttribute::VALUE => 'abc'
        ]);
        $json = json_encode($sourceAttribute);
        $rehydratedAttribute = ProductAttribute::fromArray(json_decode($json, true));
        $this->assertTrue($sourceAttribute->isCodeEqualTo($rehydratedAttribute->getCode()));
        $this->assertSame($sourceAttribute->getValue(), $rehydratedAttribute->getValue());
        $this->assertSame($sourceAttribute->getContextDataSet(), $rehydratedAttribute->getContextDataSet());
    }
}
