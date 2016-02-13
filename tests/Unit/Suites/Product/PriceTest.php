<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Product\Exception\InvalidNumberOfDecimalPointsForPriceException;

/**
 * @covers \LizardsAndPumpkins\Product\Price
 */
class PriceTest extends \PHPUnit_Framework_TestCase
{
    public function testItThrowsAnExceptionIfTheNumberOfDecimalPointsIsNotInteger()
    {
        $this->setExpectedException(
            InvalidNumberOfDecimalPointsForPriceException::class,
            'The number of decimal points for a price have to be specified as an integer, got string'
        );
        Price::fromFractions(1, '2');
    }

    public function testItThrowsAnExceptionIfTheNumberOfDecimalPointsAreNegative()
    {
        $this->setExpectedException(
            InvalidNumberOfDecimalPointsForPriceException::class,
            'The number of decimal points for a price have to be specified as a positive integer, got -2'
        );
        Price::fromFractions(1, -2);
    }

    public function testPriceIsCreatedFromStringMultiplyingItByTheNumberOfDecimalPoints()
    {
        $price = Price::fromAmount('1');
        $result = $price->getAmount();

        $this->assertSame(1000000, $result);
    }

    public function testItReturnsTheAmountAsAString()
    {
        $price = Price::fromFractions(123);
        $this->assertSame('123', (string) $price);
    }

    /**
     * @dataProvider fractionConversionDataProvider
     */
    public function testItRoundsTheAmountToGivenFractions($amount, $numDecimalPoints, $expected)
    {
        $price = Price::fromFractions($amount);
        $roundedPrice = $price->roundToFractions($numDecimalPoints);
        $this->assertSame($expected, $roundedPrice->getAmount());
    }

    /**
     * @return array[]
     */
    public function fractionConversionDataProvider()
    {
        // amount, fractions, expected
        return [
            [12345678, 6, 12345678],
            [12345678, 5, 1234568],
            [12345678, 4, 123457],
            [12345678, 3, 12346],
            [12345678, 2, 1235],
            [12345678, 1, 123],
            [12345678, 0, 12],
            [12345678, 7, 123456780],
            [12345678, 8, 1234567800],
            [19990000, 2, 1999],
        ];
    }
}
