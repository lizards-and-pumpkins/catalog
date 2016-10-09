<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Import\Product\Exception\InvalidAttributeCodeException;

/**
 * @covers \LizardsAndPumpkins\Import\Product\AttributeCode
 */
class AttributeCodeTest extends \PHPUnit_Framework_TestCase
{
    public function testItReturnsAnAttributeCodeInstance()
    {
        $attributeCode = AttributeCode::fromString('test');
        $this->assertInstanceOf(AttributeCode::class, $attributeCode);
    }

    public function testItReturnsTheInjectedCodeWhenCastToString()
    {
        $this->assertSame('test_code', (string) AttributeCode::fromString('test_code'));
    }

    /**
     * @dataProvider tooShortAttributeCodeProvider
     */
    public function testItThrowsAnExceptionIfTheAttributeCodeIsLessThenThreeCharactersLong(string $shortAttributeCode)
    {
        $this->expectException(InvalidAttributeCodeException::class);
        $this->expectExceptionMessage(
            sprintf('The attribute code has to be at least 3 characters long, got "%s"', $shortAttributeCode)
        );
        AttributeCode::fromString($shortAttributeCode);
    }

    /**
     * @return array[]
     */
    public function tooShortAttributeCodeProvider() : array
    {
        return [
            [''],
            ['a'],
            ['aa'],
        ];
    }

    /**
     * @dataProvider attributeCodeWithInvalidFirstCharacterProvider
     */
    public function testItThrowsAnExceptionIfTheFirstCharacterIsNotAThroughZ(string $attributeCode)
    {
        $this->expectException(InvalidAttributeCodeException::class);
        $this->expectExceptionMessage('The first letter of the attribute code has to be a character from a-z, got ');
        AttributeCode::fromString($attributeCode);
    }

    /**
     * @return array[]
     */
    public function attributeCodeWithInvalidFirstCharacterProvider() : array
    {
        return [
            ['Aaaaa'],
            ['Zaaaa'],
            ['0aaaa'],
            ['9aaaa'],
            ['ßaaaa'],
            ['.aaaa'],
        ];
    }

    public function testItThrowsAnExceptionIfTheAttributeCodeContainsInvalidCharacters()
    {
        $this->expectException(InvalidAttributeCodeException::class);
        $this->expectExceptionMessage(
            'The attribute code may only contain letters from a-z, numbers and underscores, got "abc."'
        );
        AttributeCode::fromString('abc.');
    }

    public function testItThrowsAnExceptionIfTheAttributeCodeEndsWithAnUnderscore()
    {
        $this->expectException(InvalidAttributeCodeException::class);
        $this->expectExceptionMessage('The attribute code may not end with an underscore, got "abc_"');
        AttributeCode::fromString('abc_');
    }

    public function testItReturnsAnAttributeCodeIfInstantiatedWithAnAttributeCode()
    {
        $attributeCode = AttributeCode::fromString(AttributeCode::fromString('test'));
        $this->assertInstanceOf(AttributeCode::class, $attributeCode);
        $this->assertEquals('test', $attributeCode);
    }

    public function testItReturnsTrueIfTheGivenCodeIsEqual()
    {
        $attributeCode = AttributeCode::fromString('test');
        $this->assertTrue($attributeCode->isEqualTo('test'));
        $this->assertTrue(AttributeCode::fromString('test')->isEqualTo($attributeCode));
    }

    public function testItReturnsFalseIfTheGivenCodeIsNotEqual()
    {
        $attributeCode = AttributeCode::fromString('foo');
        $this->assertFalse($attributeCode->isEqualTo('bar'));
        $this->assertFalse(AttributeCode::fromString('bar')->isEqualTo($attributeCode));
    }

    public function testItIsSerializable()
    {
        $this->assertInstanceOf(\JsonSerializable::class, AttributeCode::fromString('test'));
    }

    public function testItCanBeSerializedAndRehydrated()
    {
        $sourceAttributeCode = AttributeCode::fromString('test');
        $json = json_encode($sourceAttributeCode);
        $rehydratedAttributeCode = AttributeCode::fromString(json_decode($json, true));
        $this->assertTrue($sourceAttributeCode->isEqualTo($rehydratedAttributeCode));
    }
}
