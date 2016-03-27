<?php

namespace LizardsAndPumpkins\Import\Tax;

use LizardsAndPumpkins\Import\Tax\TaxableCountries;
use LizardsAndPumpkins\Import\Tax\TwentyOneRunTaxableCountries;

/**
 * @covers \LizardsAndPumpkins\Import\Tax\TwentyOneRunTaxableCountries
 */
class TwentyOneRunTaxableCountriesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TwentyOneRunTaxableCountries
     */
    private $countries;

    protected function setUp()
    {
        $this->countries = new TwentyOneRunTaxableCountries();
    }

    /**
     * @dataProvider availableCountriesDataProvider
     * @param string $availableCountry
     */
    public function testItReturnsTheAvailableCountries($availableCountry)
    {
        $this->assertContains($availableCountry, $this->countries->getCountries());
    }

    /**
     * @return array[]
     */
    public function availableCountriesDataProvider()
    {
        return [
            ['DE'],
            ['DK'],
            ['AT'],
            ['FR'],
            ['ES'],
            ['FI'],
            ['NL'],
            ['SE'],
            ['LU'],
            ['IT'],
            ['BE'],
        ];
    }

    public function testItCanBeIteratedOver()
    {
        $this->assertInstanceOf(\IteratorAggregate::class, $this->countries);
        $this->assertInstanceOf(\ArrayIterator::class, $this->countries->getIterator());
    }

    public function testItImplementsTheTaxableCountriesInterface()
    {
        $this->assertInstanceOf(TaxableCountries::class, $this->countries);
    }
}
