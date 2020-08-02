<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductRelations\ContentDelivery;

use LizardsAndPumpkins\ProductRelations\Exception\InvalidProductRelationTypeCodeException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationTypeCode
 */
class ProductRelationTypeCodeTest extends TestCase
{
    public function testItReturnsAProductRelationTypeCodeInstance(): void
    {
        $result = ProductRelationTypeCode::fromString('test');
        $this->assertInstanceOf(ProductRelationTypeCode::class, $result);
    }

    public function testItThrowsAnExceptionIfTheTypeCodeIsNotAString(): void
    {
        $this->expectException(\TypeError::class);
        ProductRelationTypeCode::fromString(123);
    }

    /**
     * @dataProvider emptyRelationTypeCodeProvider
     */
    public function testItThrowsAnExceptionIfTheProductRelationTypeCodeIsEmpty(string $emptyRelationTypeCode): void
    {
        $this->expectException(InvalidProductRelationTypeCodeException::class);
        $this->expectExceptionMessage('The product relation type code can not be empty');
        ProductRelationTypeCode::fromString($emptyRelationTypeCode);
    }

    /**
     * @return array[]
     */
    public function emptyRelationTypeCodeProvider() : array
    {
        return [
            [''],
            ['  '],
        ];
    }

    public function testItReturnsTheRelationTypeCodeAsAString(): void
    {
        $this->assertSame('test', (string) ProductRelationTypeCode::fromString('test'));
    }

    public function testItReturnsTheTrimmedTypeCodeAsAString(): void
    {
        $this->assertSame('a-code', (string) ProductRelationTypeCode::fromString(' a-code '));
    }
}
