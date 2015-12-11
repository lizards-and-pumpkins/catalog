<?php

namespace LizardsAndPumpkins\Tax;

use LizardsAndPumpkins\Country\Country;
use LizardsAndPumpkins\Website\Website;

/**
 * @covers \LizardsAndPumpkins\Tax\TwentyOneRunTaxServiceLocatorOptions
 * @uses   \LizardsAndPumpkins\Country\Country
 * @uses   \LizardsAndPumpkins\Website\Website
 */
class TwentyOneRunTaxServiceLocatorOptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TwentyOneRunTaxServiceLocatorOptions
     */
    private $taxServiceLocatorOptions;

    /**
     * @var Country
     */
    private $testCountry;

    /**
     * @var Website
     */
    private $testWebsite;

    protected function setUp()
    {
        $this->testCountry = Country::fromIso3661('de');
        $this->testWebsite = Website::fromString('test');
        $this->taxServiceLocatorOptions = new TwentyOneRunTaxServiceLocatorOptions(
            $this->testCountry,
            $this->testWebsite
        );
    }

    public function testItImplementsTheTaxServiceLocatorOptionsInterface()
    {
        $this->assertInstanceOf(TaxServiceLocatorOptions::class, $this->taxServiceLocatorOptions);
    }

    public function testItReturnsTheInjectedCountry()
    {
        $this->assertSame($this->testCountry, $this->taxServiceLocatorOptions->getCountry());
    }

    public function testItReturnsTheInjectedWebsite()
    {
        $this->assertSame($this->testWebsite, $this->taxServiceLocatorOptions->getWebsite());
    }
}
