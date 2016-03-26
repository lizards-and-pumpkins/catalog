<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Import\Product\Exception\InvalidProductIdException;
use LizardsAndPumpkins\Import\Product\ProductId;

/**
 * @covers \LizardsAndPumpkins\Import\Product\ProductId
 */
class ProductIdTest extends \PHPUnit_Framework_TestCase
{
    public function testExceptionIsThrownDuringAttemptToCreateProductIdFromNonString()
    {
        $this->expectException(InvalidProductIdException::class);
        ProductId::fromString(1);
    }

    public function testProductIdCanBeCreatedFromString()
    {
        $productId = ProductId::fromString('foo');
        $this->assertInstanceOf(ProductId::class, $productId);
    }

    public function testProductIdCanBeConvertedToString()
    {
        $productIdString = 'foo';
        $productId = ProductId::fromString($productIdString);

        $this->assertSame($productIdString, (string) $productId);
    }
}
