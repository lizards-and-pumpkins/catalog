<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Import\Product\Exception\InvalidProductAttributeValueException;
use LizardsAndPumpkins\Import\Product\Exception\ProductAttributeDoesNotContainContextPartException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Product\ProductAttribute
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 */
class ProductAttributeTest extends TestCase
{
    public function testTrueIsReturnedIfAttributeHasGivenCode(): void
    {
        $attribute = new ProductAttribute('foo', 'value', []);

        $this->assertTrue($attribute->isCodeEqualTo('foo'));
    }

    public function testFalseIsReturnedIfAttributeHasDifferentCode(): void
    {
        $attribute = new ProductAttribute('foo', 'value', []);

        $this->assertFalse($attribute->isCodeEqualTo('bar'));
    }

    public function testAttributeCodeIsReturned(): void
    {
        $attribute = new ProductAttribute('foo', 'value', []);
        
        $this->assertEquals('foo', (string) $attribute->getCode());
    }

    public function testItReturnsAnAttributeCodeInstance(): void
    {
        $attribute = new ProductAttribute('foo', 'value', []);

        $this->assertInstanceOf(AttributeCode::class, $attribute->getCode());
    }

    public function testAttributeValueIsReturned(): void
    {
        $attribute = new ProductAttribute('foo', 'bar', []);

        $this->assertEquals('bar', $attribute->getValue());
    }

    public function testItThrowsAnExceptionIfAttributeIsNotAScalar(): void
    {
        $this->expectException(InvalidProductAttributeValueException::class);
        $this->expectExceptionMessage('The product attribute "foo" has to have a scalar value, got "array"');
        $value = [
            ProductAttribute::CODE => 'bar',
            ProductAttribute::CONTEXT => [],
            ProductAttribute::VALUE => 1
        ];
        new ProductAttribute('foo', $value, []);
    }

    public function testItReturnsAProductAttributeInstanceFromArrayInput(): void
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

    public function testContextPartsOfAttributeAreReturned(): void
    {
        $contextData = ['foo' => 'bar', 'baz' => 'qux'];
        $attribute = new ProductAttribute('code', 'value', $contextData);

        $this->assertSame(array_keys($contextData), $attribute->getContextParts());
    }

    public function testExceptionIsThrownIfRequestedContextPartIsNotPresent(): void
    {
        $this->expectException(ProductAttributeDoesNotContainContextPartException::class);
        $this->expectExceptionMessage('The context part "foo" is not present on the attribute "attribute_code"');

        $attribute = new ProductAttribute('attribute_code', 'attributeValue', []);
        $attribute->getContextPartValue('foo');
    }

    public function testItReturnsTheContextPartIfItIsPresent(): void
    {
        $attribute = new ProductAttribute('attribute_code', 'attributeValue', ['foo' => 'bar']);

        $this->assertSame('bar', $attribute->getContextPartValue('foo'));
    }

    public function testFalseIsReturnedIfContentPartsOfAttributesAreDifferent(): void
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

    public function testTrueIsReturnedIfContentPartsOfAttributesAreIdentical(): void
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

    public function testFalseIsReturnedIfAttributeCodesAreDifferent(): void
    {
        $attributeA = new ProductAttribute('code_a', 'valueA', []);
        $attributeB = new ProductAttribute('code_b', 'valueB', []);

        $this->assertFalse($attributeA->isCodeEqualTo($attributeB));
    }

    public function testTrueIsReturnedIfAttributeCodesAreIdentical(): void
    {
        $attributeA = new ProductAttribute('code_a', 'valueA', []);
        $attributeB = new ProductAttribute('code_a', 'valueB', []);

        $this->assertTrue($attributeA->isCodeEqualTo($attributeB));
    }

    public function testItReturnsTheContextDataSet(): void
    {
        $contextDataSet = [
            'foo' => 'bar',
            'buz' => 'qux'
        ];
        $attribute = new ProductAttribute('test', 'abc', $contextDataSet);
        
        $this->assertSame($contextDataSet, $attribute->getContextDataSet());
    }

    public function testItIsSerializable(): void
    {
        $attribute = new ProductAttribute('test', 'abc', []);
        
        $this->assertInstanceOf(\JsonSerializable::class, $attribute);
    }

    public function testItCanBeSerializedAndRehydrated(): void
    {
        $sourceAttribute = new ProductAttribute('test', 'abc', ['foo' => 'bar']);

        $json = json_encode($sourceAttribute);
        
        $rehydratedAttribute = ProductAttribute::fromArray(json_decode($json, true));
        
        $this->assertTrue($sourceAttribute->isCodeEqualTo($rehydratedAttribute->getCode()));
        $this->assertSame($sourceAttribute->getValue(), $rehydratedAttribute->getValue());
        $this->assertSame($sourceAttribute->getContextDataSet(), $rehydratedAttribute->getContextDataSet());
    }

    public function testItIsNotEqualIfTheCodeIsDifferent(): void
    {
        $attributeOne = new ProductAttribute('test1', 'abc', []);
        $attributeTwo = new ProductAttribute('test2', 'abc', []);
        
        $this->assertFalse($attributeOne->isEqualTo($attributeTwo));
    }

    public function testItIsNotEqualIfTheValueIsDifferent(): void
    {
        $attributeOne = new ProductAttribute('test', 'abc', []);
        $attributeTwo = new ProductAttribute('test', 'def', []);
        
        $this->assertFalse($attributeOne->isEqualTo($attributeTwo));
    }

    public function testItIsNotEqualIfTheContextDataIsDifferent(): void
    {
        $attributeOne = new ProductAttribute('test', 'abc', ['foo' => 'bar1']);
        $attributeTwo = new ProductAttribute('test', 'abc', ['foo' => 'bar2']);
        
        $this->assertFalse($attributeOne->isEqualTo($attributeTwo));
    }

    public function testItIsEqualIfTheCodeAndTheValueAndTheContextDataIsEqual(): void
    {
        $attributeOne = new ProductAttribute('test', 'abc', ['foo' => 'bar']);
        $attributeTwo = new ProductAttribute('test', 'abc', ['foo' => 'bar']);
        
        $this->assertTrue($attributeOne->isEqualTo($attributeTwo));
    }
}
