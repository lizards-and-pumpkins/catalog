<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\Price
 */
class PriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldImplementMoneyInterface()
    {
        $result = new Price(0);

        $this->assertInstanceOf(Money::class, $result);
    }

    /**
     * @test
     * @expectedException \Brera\Product\InvalidArgumentException
     * @expectedExceptionMessage Can not create a price from integer
     */
    public function itShouldThrowAnExceptionIfNonStringArgumentIsPassed()
    {
        Price::fromString(1);
    }

    /**
     * @test
     * @expectedException \Brera\Product\InvalidArgumentException
     * @expectedExceptionMessage Can not create a price from string
     */
    public function itShouldThrowAnExceptionIfNonIntegerArgumentIsPassed()
    {
        new Price('1');
    }

    /**
     * @test
     */
    public function itShouldCreatePriceFromStringMultiplyingItByTheNumberOfDecimalPoints()
    {
        $price = Price::fromString('1');
        $result = $price->getAmount();

        $this->assertSame(100, $result);
    }
}
