<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\Stock
 */
class StockTest extends \PHPUnit_Framework_TestCase
{
    public function testQuantityInterfaceIsImplemented()
    {
        $result = new Stock(0);
        $this->assertInstanceOf(Quantity::class, $result);
    }

    public function testExceptionIsThrownIfNonStringArgumentIsPassedToFromStringConstructor()
    {
        $this->setExpectedException(InvalidStockSourceException::class, 'Expecting string stock source, got integer');
        Stock::fromString(1);
    }

    public function testExceptionIsThrownAsNonIntegerIsPassedToConstructor()
    {
        $this->setExpectedException(InvalidStockSourceException::class, 'Expecting integer stock source, got string');
        new Stock('1');
    }

    public function testStockIsCreatedFromStringMultipliedByNumberOfDecimalPoints()
    {
        $stock = Stock::fromString('1');
        $result = $stock->getQuantity();

        $this->assertSame(1, $result);
    }
}
