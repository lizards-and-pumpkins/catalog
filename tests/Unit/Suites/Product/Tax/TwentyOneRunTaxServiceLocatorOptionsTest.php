<?php

namespace LizardsAndPumpkins\Product\Tax;

use LizardsAndPumpkins\Country\Country;
use LizardsAndPumpkins\Website\Website;

/**
 * @covers \LizardsAndPumpkins\Product\Tax\TwentyOneRunTaxServiceLocatorOptions
 * @uses   \LizardsAndPumpkins\Product\Tax\ProductTaxClass
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

    /**
     * @var ProductTaxClass
     */
    private $testProductTaxClass;

    protected function setUp()
    {
        $this->testCountry = Country::from2CharIso3166('de');
        $this->testWebsite = Website::fromString('test');
        $this->testProductTaxClass = ProductTaxClass::fromString('test tax class');
        $this->taxServiceLocatorOptions = new TwentyOneRunTaxServiceLocatorOptions(
            $this->testWebsite,
            $this->testProductTaxClass,
            $this->testCountry
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

    public function testItReturnsTheInjectedProductTaxClass()
    {
        $this->assertSame($this->testProductTaxClass, $this->taxServiceLocatorOptions->getProductTaxClass());
    }

    public function testItReturnsAServiceLocatorOptionsInstanceFromScalars()
    {
        $websiteCode = 'website';
        $productTaxClass = 'taxclass';
        $countryCode = 'de';
        $locator = TwentyOneRunTaxServiceLocatorOptions::fromStrings($websiteCode, $productTaxClass, $countryCode);
        $this->assertInstanceOf(TwentyOneRunTaxServiceLocatorOptions::class, $locator);
    }
}
