<?php

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
     * @param mixed $nonStringTypeCode
     * @param string $expectedType
     * @dataProvider nonStringTypeCodeDataProvider
     */
    public function testItThrowsAnExceptionIfTheTypeCodeIsNotAString($nonStringTypeCode, string $expectedType)
    {
        $this->expectException(InvalidProductRelationTypeCodeException::class);
        $this->expectExceptionMessage(
            sprintf('Expected the product relation type code to be a string, got "%s"', $expectedType)
        );
        ProductRelationTypeCode::fromString($nonStringTypeCode);
    }

    /**
     * @return array[]
     */
    public function nonStringTypeCodeDataProvider() : array
    {
        return [
            [111, 'integer'],
            [[], 'array'],
            [null, 'NULL']
        ];
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
