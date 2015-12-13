<?php

namespace LizardsAndPumpkins\Product\Tax\TaxRates;

use LizardsAndPumpkins\Product\Tax\TaxRates\Exception\InvalidTaxRateException;
use LizardsAndPumpkins\Product\Tax\TaxService;

/**
 * @covers \LizardsAndPumpkins\Product\Tax\TaxRates\TwentyOneRunGenericTaxRateService
 * @uses   \LizardsAndPumpkins\Product\Tax\TaxRates\TwentyOneRunTaxRate
 */
class TwentyOneRunGenericTaxRateServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testItImplementsTheTwentyOneRunTaxRateService()
    {
        $this->assertInstanceOf(TaxService::class, TwentyOneRunGenericTaxRateService::fromInt(19));
    }

    public function testItThrowsAnExceptionIfTheTaxRateIsNotAnInteger()
    {
        $this->setExpectedException(
            InvalidTaxRateException::class,
            'The tax rate has to be an integer value, got "'
        );
        TwentyOneRunGenericTaxRateService::fromInt('10');
    }

    public function testItThrowsAnExceptionIfTheTaxRateIsZero()
    {
        $this->setExpectedException(
            InvalidTaxRateException::class,
            'The tax rate must not be zero'
        );
        TwentyOneRunGenericTaxRateService::fromInt(0);
    }

    public function testItReturnsTheInjectedFactor()
    {
        $this->assertSame(19, TwentyOneRunGenericTaxRateService::fromInt(19)->getRate());
    }
}
