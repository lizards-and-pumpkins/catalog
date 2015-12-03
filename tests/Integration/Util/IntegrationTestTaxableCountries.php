<?php


namespace LizardsAndPumpkins;

class IntegrationTestTaxableCountries implements TaxableCountries
{
    private static $countries = ['DE', 'FR'];

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator(self::$countries);
    }

    /**
     * @return string[]
     */
    public function getCountries()
    {
        return self::$countries;
    }
}
