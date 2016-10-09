<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductRelations\ContentDelivery;

use LizardsAndPumpkins\ProductRelations\Exception\InvalidProductRelationTypeCodeException;

/**
 * @covers \LizardsAndPumpkins\ProductRelations\ContentDelivery\ProductRelationTypeCode
 */
class ProductRelationTypeCodeTest extends \PHPUnit_Framework_TestCase
{
    public function testItReturnsAProductRelationTypeCodeInstance()
    {
        $result = ProductRelationTypeCode::fromString('test');
        $this->assertInstanceOf(ProductRelationTypeCode::class, $result);
    }

    /**
     * @dataProvider emptyRelationTypeCodeProvider
     */
    public function testItThrowsAnExceptionIfTheProductRelationTypeCodeIsEmpty(string $emptyRelationTypeCode)
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

    public function testItReturnsTheRelationTypeCodeAsAString()
    {
        $this->assertSame('test', (string) ProductRelationTypeCode::fromString('test'));
    }

    public function testItReturnsTheTrimmedTypeCodeAsAString()
    {
        $this->assertSame('a-code', (string) ProductRelationTypeCode::fromString(' a-code '));
    }
}
