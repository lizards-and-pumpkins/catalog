<?php


namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\Exception\InvalidProductTypeCodeException;
use LizardsAndPumpkins\Import\Product\ProductTypeCode;
use LizardsAndPumpkins\Import\Product\SimpleProduct;

/**
 * @covers \LizardsAndPumpkins\Import\Product\ProductTypeCode
 */
class ProductTypeCodeTest extends \PHPUnit_Framework_TestCase
{
    public function testItThrowsAnExceptionIfTheTypeIsNotAString()
    {
        $this->expectException(InvalidProductTypeCodeException::class);
        $this->expectExceptionMessage('The product type code has to be a string, got "integer"');
        ProductTypeCode::fromString(123);
    }

    public function testItThrowsAnExceptionIfTheTypeStringIsEmpty()
    {
        $this->expectException(InvalidProductTypeCodeException::class);
        $this->expectExceptionMessage('The product type code can not be empty');
        ProductTypeCode::fromString('');
    }

    public function testItTrimsWhitespaceWhenCheckingIfEmpty()
    {
        $this->expectException(InvalidProductTypeCodeException::class);
        $this->expectExceptionMessage('The product type code can not be empty');
        ProductTypeCode::fromString(' ');
    }

    public function testItReturnsAProductTypeIdentifierInstance()
    {
        $this->assertInstanceOf(ProductTypeCode::class, ProductTypeCode::fromString(SimpleProduct::TYPE_CODE));
    }

    /**
     * @param string $typeString
     * @dataProvider validProductTypeStringProvider
     */
    public function testItReturnsTheTypeStringWhenCastToString($typeString)
    {
        $this->assertSame($typeString, (string) ProductTypeCode::fromString($typeString));
    }

    /**
     * @return array[]
     */
    public function validProductTypeStringProvider()
    {
        return [[SimpleProduct::TYPE_CODE], [ConfigurableProduct::TYPE_CODE], ['test']];
    }

    public function testItReturnsTrueForEqualProductTypeCodes()
    {
        $productTypeCodeInstanceOne = ProductTypeCode::fromString('test');
        $productTypeCodeInstanceTwo = ProductTypeCode::fromString('test');
        $this->assertTrue($productTypeCodeInstanceOne->isEqualTo($productTypeCodeInstanceTwo));
    }

    public function testItReturnsFalseForDifferentProductTypeCodes()
    {
        $productTypeCodeInstanceOne = ProductTypeCode::fromString('aaa');
        $productTypeCodeInstanceTwo = ProductTypeCode::fromString('bbb');
        $this->assertFalse($productTypeCodeInstanceOne->isEqualTo($productTypeCodeInstanceTwo));
    }
}
