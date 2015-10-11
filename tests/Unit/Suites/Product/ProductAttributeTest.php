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
        $attribute = new ProductAttribute('foo', 'value', []);

        $this->assertTrue($attribute->isCodeEqualTo('foo'));
    }

    public function testFalseIsReturnedIfAttributeHasDifferentCode()
    {

        $attribute = new ProductAttribute('foo', 'value', []);

        $this->assertFalse($attribute->isCodeEqualTo('bar'));
    }

    public function testAttributeCodeIsReturned()
    {
        $attribute = new ProductAttribute('foo', 'value', []);
        
        $this->assertEquals('foo', (string) $attribute->getCode());
    }

    public function testItReturnsAnAttributeCodeInstance()
    {
        $attribute = new ProductAttribute('foo', 'value', []);

        $this->assertInstanceOf(AttributeCode::class, $attribute->getCode());
    }

    public function testAttributeValueIsReturned()
    {
        $attribute = new ProductAttribute('foo', 'bar', []);

        $this->assertEquals('bar', $attribute->getValue());
    }

    public function testItThrowsAnExceptionIfAttributeIsNotAScalar()
    {
        $this->setExpectedException(
            InvalidProductAttributeValueException::class,
            'The product attribute "foo" has to have a scalar value, got "array"'
        );
        $value = [
            ProductAttribute::CODE => 'bar',
            ProductAttribute::CONTEXT => [],
            ProductAttribute::VALUE => 1
        ];
        new ProductAttribute('foo', $value, []);
    }

    public function testItReturnsAProductAttributeInstanceFromArrayInput()
    {
        $attribute = ProductAttribute::fromArray([
            ProductAttribute::CODE => 'attribute_code',
            ProductAttribute::CONTEXT => [],
            ProductAttribute::VALUE => 'attribute_value'
        ]);
        $this->assertInstanceOf(ProductAttribute::class, $attribute);
        $this->assertEquals('attribute_code', $attribute->getCode());
        $this->assertSame('attribute_value', $attribute->getValue());
        $this->assertSame([], $attribute->getContextDataSet());
    }

    public function testContextPartsOfAttributeAreReturned()
    {
        $contextData = ['foo' => 'bar', 'baz' => 'qux'];
        $attribute = new ProductAttribute('code', 'value', $contextData);

        $this->assertSame(array_keys($contextData), $attribute->getContextParts());
    }

    public function testExceptionIsThrownIfRequestedContextPartIsNotPresent()
    {
        $this->setExpectedException(
            ProductAttributeDoesNotContainContextPartException::class,
            'The context part "foo" is not present on the attribute "attribute_code"'
        );
        
        $attribute = new ProductAttribute('attribute_code', 'attributeValue', []);
        $attribute->getContextPartValue('foo');
    }

    public function testItReturnsTheContextPartIfItIsPresent()
    {
        $attribute = new ProductAttribute('attribute_code', 'attributeValue', ['foo' => 'bar']);

        $this->assertSame('bar', $attribute->getContextPartValue('foo'));
    }

    public function testFalseIsReturnedIfContentPartsOfAttributesAreDifferent()
    {
        $attributeA = new ProductAttribute('attribute_a_code', 'attributeAValue', [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);
        $attributeB = new ProductAttribute('attribute_b_code', 'attributeBValue', [
            'foo' => 'bar',
        ]);

        $this->assertFalse($attributeA->hasSameContextPartsAs($attributeB));
    }

    public function testTrueIsReturnedIfContentPartsOfAttributesAreIdentical()
    {
        $attributeA = new ProductAttribute('attribute_a_code', 'attributeAValue', [
            'foo' => 'bar',
            'baz' => 'qux',
        ]);
        $attributeB = new ProductAttribute('attribute_b_code', 'attributeBValue', [
            'foo' => 'qux',
            'baz' => 'bar',
        ]);

        $this->assertTrue($attributeA->hasSameContextPartsAs($attributeB));
    }

    public function testFalseIsReturnedIfAttributeCodesAreDifferent()
    {
        $attributeA = new ProductAttribute('code_a', 'valueA', []);
        $attributeB = new ProductAttribute('code_b', 'valueB', []);

        $this->assertFalse($attributeA->isCodeEqualTo($attributeB));
    }

    public function testTrueIsReturnedIfAttributeCodesAreIdentical()
    {
        $attributeA = new ProductAttribute('code_a', 'valueA', []);
        $attributeB = new ProductAttribute('code_a', 'valueB', []);

        $this->assertTrue($attributeA->isCodeEqualTo($attributeB));
    }

    public function testItReturnsTheContextDataSet()
    {
        $contextDataSet = [
            'foo' => 'bar',
            'buz' => 'qux'
        ];
        $attribute = new ProductAttribute('test', 'abc', $contextDataSet);
        
        $this->assertSame($contextDataSet, $attribute->getContextDataSet());
    }

    public function testItIsSerializable()
    {
        $attribute = new ProductAttribute('test', 'abc', []);
        
        $this->assertInstanceOf(\JsonSerializable::class, $attribute);
    }

    public function testItCanBeSerializedAndRehydrated()
    {
        $sourceAttribute = new ProductAttribute('test', 'abc', ['foo' => 'bar']);

        $json = json_encode($sourceAttribute);
        
        $rehydratedAttribute = ProductAttribute::fromArray(json_decode($json, true));
        
        $this->assertTrue($sourceAttribute->isCodeEqualTo($rehydratedAttribute->getCode()));
        $this->assertSame($sourceAttribute->getValue(), $rehydratedAttribute->getValue());
        $this->assertSame($sourceAttribute->getContextDataSet(), $rehydratedAttribute->getContextDataSet());
    }

    public function testItIsNotEqualIfTheCodeIsDifferent()
    {
        $attributeOne = new ProductAttribute('test1', 'abc', []);
        $attributeTwo = new ProductAttribute('test2', 'abc', []);
        
        $this->assertFalse($attributeOne->isEqualTo($attributeTwo));
    }

    public function testItIsNotEqualIfTheValueIsDifferent()
    {
        $attributeOne = new ProductAttribute('test', 'abc', []);
        $attributeTwo = new ProductAttribute('test', 'def', []);
        
        $this->assertFalse($attributeOne->isEqualTo($attributeTwo));
    }

    public function testItIsNotEqualIfTheContextDataIsDifferent()
    {
        $attributeOne = new ProductAttribute('test', 'abc', ['foo' => 'bar1']);
        $attributeTwo = new ProductAttribute('test', 'abc', ['foo' => 'bar2']);
        
        $this->assertFalse($attributeOne->isEqualTo($attributeTwo));
    }

    public function testItIsEqualIfTheCodeAndTheValueAndTheContextDataIsEqual()
    {
        $attributeOne = new ProductAttribute('test', 'abc', ['foo' => 'bar']);
        $attributeTwo = new ProductAttribute('test', 'abc', ['foo' => 'bar']);
        
        $this->assertTrue($attributeOne->isEqualTo($attributeTwo));
    }
}
