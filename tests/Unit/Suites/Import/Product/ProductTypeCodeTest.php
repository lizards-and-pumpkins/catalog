<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\Exception\InvalidProductTypeCodeException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Product\ProductTypeCode
 */
class ProductTypeCodeTest extends TestCase
{
    public function testItThrowsAnExceptionIfTheTypeIsNotAString(): void
    {
        $this->expectException(\TypeError::class);
        ProductTypeCode::fromString(123);
    }

    public function testItThrowsAnExceptionIfTheTypeStringIsEmpty(): void
    {
        $this->expectException(InvalidProductTypeCodeException::class);
        $this->expectExceptionMessage('The product type code can not be empty');
        ProductTypeCode::fromString('');
    }

    public function testItTrimsWhitespaceWhenCheckingIfEmpty(): void
    {
        $this->expectException(InvalidProductTypeCodeException::class);
        $this->expectExceptionMessage('The product type code can not be empty');
        ProductTypeCode::fromString(' ');
    }

    public function testItReturnsAProductTypeIdentifierInstance(): void
    {
        $this->assertInstanceOf(ProductTypeCode::class, ProductTypeCode::fromString(SimpleProduct::TYPE_CODE));
    }

    /**
     * @dataProvider validProductTypeStringProvider
     */
    public function testItReturnsTheTypeStringWhenCastToString(string $typeString): void
    {
        $this->assertSame($typeString, (string) ProductTypeCode::fromString($typeString));
    }

    /**
     * @return array[]
     */
    public function validProductTypeStringProvider() : array
    {
        return [[SimpleProduct::TYPE_CODE], [ConfigurableProduct::TYPE_CODE], ['test']];
    }

    public function testItReturnsTrueForEqualProductTypeCodes(): void
    {
        $productTypeCodeInstanceOne = ProductTypeCode::fromString('test');
        $productTypeCodeInstanceTwo = ProductTypeCode::fromString('test');
        $this->assertTrue($productTypeCodeInstanceOne->isEqualTo($productTypeCodeInstanceTwo));
    }

    public function testItReturnsFalseForDifferentProductTypeCodes(): void
    {
        $productTypeCodeInstanceOne = ProductTypeCode::fromString('aaa');
        $productTypeCodeInstanceTwo = ProductTypeCode::fromString('bbb');
        $this->assertFalse($productTypeCodeInstanceOne->isEqualTo($productTypeCodeInstanceTwo));
    }
}
