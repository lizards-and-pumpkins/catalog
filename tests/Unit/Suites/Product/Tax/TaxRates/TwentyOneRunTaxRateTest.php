<?php

namespace LizardsAndPumpkins\Product\Tax\TaxRates;

use LizardsAndPumpkins\Product\Price;
use LizardsAndPumpkins\Product\Tax\TaxRates\Exception\InvalidTaxRateException;
use LizardsAndPumpkins\Product\Tax\TaxService;

/**
 * @covers \LizardsAndPumpkins\Product\Tax\TaxRates\TwentyOneRunTaxRate
 * @uses \LizardsAndPumpkins\Product\Price
 */
class TwentyOneRunTaxRateTest extends \PHPUnit_Framework_TestCase
{
    public function testItImplementsTheTwentyOneRunTaxRateService()
    {
        $this->assertInstanceOf(TaxService::class, TwentyOneRunTaxRate::fromInt(19));
    }

    public function testItReturnsATaxServiceInstanceMatchingTheGivenRate()
    {
        $this->assertInstanceOf(TwentyOneRunTaxRate::class, TwentyOneRunTaxRate::fromInt(20));
        $this->assertInstanceOf(TwentyOneRunTaxRate::class, TwentyOneRunTaxRate::fromInt(19));
        $this->assertInstanceOf(TwentyOneRunTaxRate::class, TwentyOneRunTaxRate::fromInt(18));
    }

    public function testItThrowsAnExceptionIfTheTaxRateIsNotAnInteger()
    {
        $this->expectException(InvalidTaxRateException::class);
        $this->expectExceptionMessage('The tax rate has to be an integer value, got "');
        TwentyOneRunTaxRate::fromInt('10');
    }

    public function testItThrowsAnExceptionIfTheTaxRateIsZero()
    {
        $this->expectException(InvalidTaxRateException::class);
        $this->expectExceptionMessage('The tax rate must not be zero');
        TwentyOneRunTaxRate::fromInt(0);
    }

    public function testItReturnsTheInjectedFactor()
    {
        $this->assertSame(19, TwentyOneRunTaxRate::fromInt(19)->getRate());
    }

    /**
     * @param int $rate
     * @param int $price
     * @param int $expected
     * @dataProvider taxRateExampleProvider
     */
    public function testItAppliesTheTaxRate($rate, $price, $expected)
    {
        $result = TwentyOneRunTaxRate::fromInt($rate)->applyTo(Price::fromFractions($price));
        $message = sprintf('Expected tax rate %s applied to %d to be %s, got %s', $rate, $price, $expected, $result);
        $this->assertSame($expected, $result->getAmount(), $message);
    }

    /**
     * @return array[]
     */
    public function taxRateExampleProvider()
    {
        // rate, price, expected
        return [
            [19, 1000, 1190],
            [19, 125, 149],
            [19, 124, 148],
            [19, 123, 146],
            [19, 100, 119],
            [19, 10, 12],
            [19, 3, 4],
            [19, 2, 2],
            [19, 1, 1],
            [19, 0, 0],
            [19, -1, -1],

            [25, 1000, 1250],
            [25, 125, 156],
            [25, 124, 155],
            [25, 123, 154],
            [25, 100, 125],
            [25, 10, 12],
            [25, 3, 4],
            [25, 2, 2],
            [25, 1, 1],
            [25, 0, 0],
            [25, -1, -1],

            [20, 1000, 1200],
            [20, 125, 150],
            [20, 124, 149],
            [20, 123, 148],
            [20, 100, 120],
            [20, 10, 12],
            [20, 3, 4],
            [20, 2, 2],
            [20, 1, 1],
            [20, 0, 0],
            [20, -1, -1],

            [21, 1000, 1210],
            [21, 125, 151],
            [21, 124, 150],
            [21, 123, 149],
            [21, 100, 121],
            [21, 10, 12],
            [21, 3, 4],
            [21, 2, 2],
            [21, 1, 1],
            [21, 0, 0],
            [21, -1, -1],

            [24, 1000, 1240],
            [24, 125, 155],
            [24, 124, 154],
            [24, 123, 153],
            [24, 100, 124],
            [24, 10, 12],
            [24, 3, 4],
            [24, 2, 2],
            [24, 1, 1],
            [24, 0, 0],
            [24, -1, -1],

            [17, 1000, 1170],
            [17, 125, 146],
            [17, 124, 145],
            [17, 123, 144],
            [17, 100, 117],
            [17, 10, 12],
            [17, 3, 4],
            [17, 2, 2],
            [17, 1, 1],
            [17, 0, 0],
            [17, -1, -1],

            [7, 1000, 1070],
            [7, 125, 134],
            [7, 124, 133],
            [7, 123, 132],
            [7, 100, 107],
            [7, 10, 11],
            [7, 3, 3],
            [7, 2, 2],
            [7, 1, 1],
            [7, 0, 0],
            [7, -1, -1],
        ];
    }
}
