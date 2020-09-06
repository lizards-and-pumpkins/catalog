<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Product\ProductId
 */
class ProductIdTest extends TestCase
{
    public function testExceptionIsThrownDuringAttemptToCreateProductIdFromNonString(): void
    {
        $this->expectException(\TypeError::class);
        new ProductId(1);
    }

    public function testProductIdCanBeCreatedFromString(): void
    {
        $productId = new ProductId('foo');
        $this->assertInstanceOf(ProductId::class, $productId);
    }

    public function testProductIdCanBeConvertedToString(): void
    {
        $productIdString = 'foo';
        $productId = new ProductId($productIdString);

        $this->assertSame($productIdString, (string) $productId);
    }
}
