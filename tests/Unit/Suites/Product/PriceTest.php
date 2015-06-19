<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\Price
 */
class PriceTest extends \PHPUnit_Framework_TestCase
{
    public function testMoneyInterfaceIsImplemented()
    {
        $this->assertInstanceOf(Money::class, new Price(0));
    }

    public function testExceptionIsThrownIfNonStringArgumentIsPassed()
    {
        $this->setExpectedException(InvalidPriceSourceException::class, 'Can not create a price from integer');
        Price::fromString(1);
    }

    public function testExceptionIsThrownIfNonIntegerArgumentIsPassed()
    {
        $this->setExpectedException(InvalidPriceSourceException::class, 'Can not create a price from string');
        new Price('1');
    }

    public function testPriceIsCreatedFromStringMultiplyingItByTheNumberOfDecimalPoints()
    {
        $price = Price::fromString('1');
        $result = $price->getAmount();

        $this->assertSame(100, $result);
    }
}
