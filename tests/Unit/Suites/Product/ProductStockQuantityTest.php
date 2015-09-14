<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Product\Exception\InvalidStockQuantitySourceException;

/**
 * @covers \LizardsAndPumpkins\Product\ProductStockQuantity
 */
class ProductStockQuantityTest extends \PHPUnit_Framework_TestCase
{
    public function testQuantityInterfaceIsImplemented()
    {
        $result = ProductStockQuantity::fromInt(0);
        $this->assertInstanceOf(Quantity::class, $result);
    }

    public function testExceptionIsThrownIfNonStringArgumentIsPassedToFromStringConstructor()
    {
        $this->setExpectedException(
            InvalidStockQuantitySourceException::class,
            'Expecting string stock source, got integer'
        );
        ProductStockQuantity::fromString(1);
    }

    public function testExceptionIsThrownAsNonIntegerIsPassedToFromIntConstructor()
    {
        $this->setExpectedException(
            InvalidStockQuantitySourceException::class,
            'Expecting integer stock source, got string'
        );
        ProductStockQuantity::fromInt('1');
    }

    public function testStockIsCreatedFromStringMultipliedByNumberOfDecimalPoints()
    {
        $stock = ProductStockQuantity::fromString('1');
        $result = $stock->getQuantity();

        $this->assertSame(1, $result);
    }
}
