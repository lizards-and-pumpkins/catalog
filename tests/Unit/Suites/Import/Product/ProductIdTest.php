<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Product\ProductId
 */
class ProductIdTest extends TestCase
{
    public function testExceptionIsThrownDuringAttemptToCreateProductIdFromNonString()
    {
        $this->expectException(\TypeError::class);
        new ProductId(1);
    }

    public function testProductIdCanBeCreatedFromString()
    {
        $productId = new ProductId('foo');
        $this->assertInstanceOf(ProductId::class, $productId);
    }

    public function testProductIdCanBeConvertedToString()
    {
        $productIdString = 'foo';
        $productId = new ProductId($productIdString);

        $this->assertSame($productIdString, (string) $productId);
    }
}
