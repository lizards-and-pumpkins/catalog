<?php


namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Product\Exception\InvalidAttributeCodeException;

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

    public function testItThrowsAnExceptionIfTheCodeIsNotAString()
    {
        $this->setExpectedException(
            InvalidAttributeCodeException::class,
            'The attribute code has to be a string, got "integer"'
        );
        AttributeCode::fromString(222);
    }

    public function testItThrowsAnExceptionIfTheAttributeCodeIsLessThenThreeCharactersLong()
    {
        $this->setExpectedException(
            InvalidAttributeCodeException::class,
            'The attribute code has to be at least 3 characters long, got ""'
        );
        AttributeCode::fromString('');
    }

    /**
     * @dataProvider attributeCodeWithInvalidFirstCharacterProvider
     */
    public function testItThrowsAnExceptionIfTheFirstCharacterIsNotAThroughZ($attributeCode)
    {
        $this->setExpectedException(
            InvalidAttributeCodeException::class,
            'The first letter of the attribute code has to be a character from a-z, got '
        );
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
        $this->setExpectedException(
            InvalidAttributeCodeException::class,
            'The attribute code may only contain letters from a-z, numbers and underscores, got "abc."'
        );
        AttributeCode::fromString('abc.');
    }

    public function testItThrowsAnExceptionIfTheAttributeCodeEndsWithAnUnderscore()
    {
        $this->setExpectedException(
            InvalidAttributeCodeException::class,
            'The attribute code may not and with an underscore, got "abc_"'
        );
        AttributeCode::fromString('abc_');
    }

    public function testItIsSerializable()
    {
        $this->assertInstanceOf(\JsonSerializable::class, AttributeCode::fromString('test'));
    }

    public function testItReturnsTheAttributeCodeAsJson()
    {
        $this->assertSame('"test"', json_encode(AttributeCode::fromString('test')));
    }
}
