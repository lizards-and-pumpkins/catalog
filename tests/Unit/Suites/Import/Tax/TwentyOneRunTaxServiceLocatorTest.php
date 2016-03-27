<?php

namespace LizardsAndPumpkins\Import\Tax;

use LizardsAndPumpkins\Import\Tax\TaxServiceLocator;
use LizardsAndPumpkins\Import\Tax\TwentyOneRunTaxServiceLocator;
use LizardsAndPumpkins\Import\Tax\UnableToLocateTaxServiceException;
use LizardsAndPumpkins\Import\Tax\TwentyOneRunTaxRate;

/**
 * @covers \LizardsAndPumpkins\Import\Tax\TwentyOneRunTaxServiceLocator
 * @uses   \LizardsAndPumpkins\Import\Tax\ProductTaxClass
 * @uses   \LizardsAndPumpkins\Context\Country\Country
 * @uses   \LizardsAndPumpkins\Context\Website\Website
 * @uses   \LizardsAndPumpkins\Import\Tax\TwentyOneRunTaxRate
 */
class TwentyOneRunTaxServiceLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TwentyOneRunTaxServiceLocator
     */
    private $taxServiceLocator;

    /**
     * @param string $website
     * @param string $taxClass
     * @param string $country
     * @return mixed[]
     */
    private function createTaxServiceLocatorOptions($website, $taxClass, $country)
    {
        return [
            TaxServiceLocator::OPTION_PRODUCT_TAX_CLASS => $taxClass,
            TaxServiceLocator::OPTION_COUNTRY => $country,
            TaxServiceLocator::OPTION_WEBSITE => $website
        ];
    }

    /**
     * @param string $website
     * @param string $productTaxClass
     * @param string $country
     * @param string $expectedRate
     */
    private function assertTaxServiceLocatorReturns($website, $productTaxClass, $country, $expectedRate)
    {
        $taxService = $this->getTaxServiceFor($website, $productTaxClass, $country);
        $this->assertInstanceOf(TwentyOneRunTaxRate::class, $taxService);
        $message = sprintf(
            'Expected the tax rate for website "%s", tax class "%s" and country "%s" to be "%s", got "%s"',
            $website,
            $productTaxClass,
            $country,
            $expectedRate,
            $taxService->getRate()
        );
        $this->assertSame($expectedRate, $taxService->getRate(), $message);
    }

    /**
     * @param string $website
     * @param string $productTaxClass
     * @param string $country
     * @return TwentyOneRunTaxRate
     */
    private function getTaxServiceFor($website, $productTaxClass, $country)
    {
        $options = $this->createTaxServiceLocatorOptions($website, $productTaxClass, $country);
        return $this->taxServiceLocator->get($options);
    }

    protected function setUp()
    {
        $this->taxServiceLocator = new TwentyOneRunTaxServiceLocator();
    }

    public function testItImplementsTheTaxServiceLocatorInterface()
    {
        $this->assertInstanceOf(TaxServiceLocator::class, $this->taxServiceLocator);
    }

    public function testItThrowsAnExceptionIfTheTaxServiceCanNotBeDetermined()
    {
        $this->expectException(UnableToLocateTaxServiceException::class);
        $this->expectExceptionMessage(
            'Unable to locate a tax service for website "test", product tax class "tax class" and country "GG"'
        );

        $website = 'test';
        $taxClass = 'tax class';
        $country = 'GG';
        $this->taxServiceLocator->get($this->createTaxServiceLocatorOptions($website, $taxClass, $country));
    }

    /**
     * @dataProvider taxServiceLocatorOptionsProvider
     * @param string $website
     * @param string $productTaxClass
     * @param string $country
     * @param string $rate
     */
    public function testTaxServiceLocatorReturnsTheCorrectInstances($website, $productTaxClass, $country, $rate)
    {
        $this->assertTaxServiceLocatorReturns($website, $productTaxClass, $country, $rate);
    }

    /**
     * @return array[]
     */
    public function taxServiceLocatorOptionsProvider()
    {
        return [

            // ------ "19%" tax class -------
            
            ['ru', '19%', 'DE', 19],
            ['fr', '19%', 'DE', 19],
            
            ['ru', '19%', 'DK', 25],
            ['fr', '19%', 'DK', 25],
            
            ['ru', '19%', 'AT', 20],
            ['fr', '19%', 'AT', 20],
            
            ['ru', '19%', 'FR', 20],
            ['fr', '19%', 'FR', 20],
            
            ['ru', '19%', 'ES', 21],
            ['fr', '19%', 'ES', 21],
            
            ['ru', '19%', 'FI', 24],
            ['fr', '19%', 'FI', 24],
            
            ['ru', '19%', 'NL', 21],
            ['fr', '19%', 'NL', 21],
            
            ['ru', '19%', 'SE', 25],
            ['fr', '19%', 'SE', 25],
            
            ['ru', '19%', 'LU', 17],
            ['fr', '19%', 'LU', 17],
            
            ['ru', '19%', 'IT', 21],
            ['fr', '19%', 'IT', 21],
            
            ['ru', '19%', 'BE', 21],
            ['fr', '19%', 'BE', 21],
            
            // ------ "7%" tax class -------

            ['ru', '7%', 'DE', 7],
            ['fr', '7%', 'DE', 7],
            
            ['ru', '7%', 'DK', 25],
            ['fr', '7%', 'DK', 25],
            
            ['ru', '7%', 'AT', 20],
            ['fr', '7%', 'AT', 20],
            
            ['ru', '7%', 'FR', 20],
            ['fr', '7%', 'FR', 20],
            
            ['ru', '7%', 'ES', 21],
            ['fr', '7%', 'ES', 21],
            
            ['ru', '7%', 'FI', 24],
            ['fr', '7%', 'FI', 24],
            
            ['ru', '7%', 'NL', 21],
            ['fr', '7%', 'NL', 21],
            
            ['ru', '7%', 'SE', 25],
            ['fr', '7%', 'SE', 25],
            
            ['ru', '7%', 'LU', 17],
            ['fr', '7%', 'LU', 17],
            
            ['ru', '7%', 'IT', 21],
            ['fr', '7%', 'IT', 21],
            
            ['ru', '7%', 'BE', 21],
            ['fr', '7%', 'BE', 21],

            // ------ "21cycles.com" tax class -------

            ['cy', '21cycles.com', 'DE', 19],

            // ------ "VR 7%" tax class -------
            
            ['cy', 'VR 7%', 'DE', 7],
        ];
    }
}
