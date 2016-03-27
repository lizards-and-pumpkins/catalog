<?php

namespace LizardsAndPumpkins\Import\Price;

use LizardsAndPumpkins\Import\Price\Exception\InvalidNumberOfDecimalPointsForPriceException;
use LizardsAndPumpkins\Import\Price\Price;

/**
 * @covers \LizardsAndPumpkins\Import\Price\Price
 */
class PriceTest extends \PHPUnit_Framework_TestCase
{
    public function testItThrowsAnExceptionIfTheNumberOfDecimalPointsIsNotInteger()
    {
        $this->expectException(InvalidNumberOfDecimalPointsForPriceException::class);
        $this->expectExceptionMessage(
            'The number of decimal points for a price have to be specified as an integer, got string'
        );
        Price::fromFractionsWithDecimalPlaces(1, '2');
    }

    public function testItThrowsAnExceptionIfTheNumberOfDecimalPointsAreNegative()
    {
        $this->expectException(InvalidNumberOfDecimalPointsForPriceException::class);
        $this->expectExceptionMessage(
            'The number of decimal points for a price have to be specified as a positive integer, got -2'
        );
        Price::fromFractionsWithDecimalPlaces(1, -2);
    }

    public function testPriceIsCreatedFromStringMultiplyingItByTheNumberOfDecimalPoints()
    {
        $price = Price::fromDecimalValue('1');
        $result = $price->getAmount();

        $expected = pow(10, Price::DEFAULT_DECIMAL_PLACES);
        $this->assertSame($expected, $result);
    }

    public function testItReturnsTheAmountAsAString()
    {
        $price = Price::fromFractions(123);
        $this->assertSame('123', (string) $price);
    }

    /**
     * @param int $amount
     * @param int $numDecimalPoints
     * @param int $expected
     * @dataProvider fractionConversionDataProvider
     */
    public function testItRoundsTheAmountToGivenFractions($amount, $numDecimalPoints, $expected)
    {
        $price = Price::fromFractionsWithDecimalPlaces($amount, 6);
        $roundedPrice = $price->round($numDecimalPoints);
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

    /**
     * @dataProvider priceMultiplicationDataProvider
     */
    public function testItMultipliesByTheGivenFactor($amount, $factor, $expected)
    {
        $price = Price::fromFractions($amount);
        $result = $price->multiplyBy($factor);
        $this->assertSame($expected, $result->getAmount());
    }

    /**
     * @return array[]
     */
    public function priceMultiplicationDataProvider()
    {
        // amount, factor, expected
        return [
            [100, 0, 0],
            [100, 1, 100],
            [100, -1, -100],
            [100, 1.26, 126],
            [1000000, 1.234567, 1234567],
            [2176470588, 1.2, 2611764706],
        ];
    }

    public function testItHasEnoughPrecision()
    {
        $price = Price::fromDecimalValue('21.76470588');
        $this->assertSame('2612', (string) $price->multiplyBy(1.2)->round(2));
    }
}
