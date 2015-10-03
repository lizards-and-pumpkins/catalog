<?php


namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Product\Exception\InvalidProductTypeIdentifierException;

class ProductTypeCodeTest extends \PHPUnit_Framework_TestCase
{
    public function testItThrowsAnExceptionIfTheTypeIsNotAString()
    {
        $this->setExpectedException(
            InvalidProductTypeIdentifierException::class,
            'The product type identifier has to be a string, got "integer"'
        );
        ProductTypeCode::fromString(123);
    }

    public function testItThrowsAnExceptionIfTheTypeStringIsEmpty()
    {
        $this->setExpectedException(
            InvalidProductTypeIdentifierException::class,
            'The product type identifier can not be empty'
        );
        ProductTypeCode::fromString('');
    }

    public function testItThrowsAnExceptionIfTheProductTypeStringIsNotValid()
    {
        $this->setExpectedException(
            InvalidProductTypeIdentifierException::class,
            'The product type identifier "test" is invalid, expected one of "simple", "configurable"'
        );
        ProductTypeCode::fromString('test');
    }

    public function testItReturnsAProductTypeIdentifierInstance()
    {
        $this->assertInstanceOf(ProductTypeCode::class, ProductTypeCode::fromString('simple'));
    }

    /**
     * @param string $typeString
     * @dataProvider validProductTypeStringProvider
     */
    public function testItReturnsTheTypeStringWhenCastToString($typeString)
    {
        $this->assertSame($typeString, (string)ProductTypeCode::fromString($typeString));
    }

    /**
     * @return array[]
     */
    public function validProductTypeStringProvider()
    {
        return [['simple'], ['configurable']];
    }
}
