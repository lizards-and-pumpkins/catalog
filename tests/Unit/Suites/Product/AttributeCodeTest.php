<?php


namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Product\Exception\InvalidAttributeCodeException;

/**
 * @covers LizardsAndPumpkins\Product\AttributeCode
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
     * @dataProvider invalidAttributeCodeTypeProvider
     * @param string $invalidAttributeCode
     */
    public function testItThrowsAnExceptionIfTheCodeIsNotAString($invalidAttributeCode)
    {
        $type = is_object($invalidAttributeCode) ?
            get_class($invalidAttributeCode) :
            gettype($invalidAttributeCode);
        $this->expectException(InvalidAttributeCodeException::class);
        $this->expectExceptionMessage(sprintf('The attribute code has to be a string, got "%s"', $type));
        AttributeCode::fromString($invalidAttributeCode);
    }

    /**
     * @return array[]
     */
    public function invalidAttributeCodeTypeProvider()
    {
        return [
            'integer' => [222],
            'null' => [null],
            'array' => [['foo']],
            'object' => [new \stdClass],
            'float' => [2.2]
        ];
    }

    /**
     * @param string $shortAttributeCode
     * @dataProvider tooShortAttributeCodeProvider
     */
    public function testItThrowsAnExceptionIfTheAttributeCodeIsLessThenThreeCharactersLong($shortAttributeCode)
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
    public function tooShortAttributeCodeProvider()
    {
        return [
            [''],
            ['a'],
            ['aa'],
        ];
    }

    /**
     * @param string $attributeCode
     * @dataProvider attributeCodeWithInvalidFirstCharacterProvider
     */
    public function testItThrowsAnExceptionIfTheFirstCharacterIsNotAThroughZ($attributeCode)
    {
        $this->expectException(InvalidAttributeCodeException::class);
        $this->expectExceptionMessage('The first letter of the attribute code has to be a character from a-z, got ');
        AttributeCode::fromString($attributeCode);
    }

    /**
     * @return array[]
     */
    public function attributeCodeWithInvalidFirstCharacterProvider()
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
