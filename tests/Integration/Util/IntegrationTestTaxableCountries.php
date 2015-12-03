<?php


namespace LizardsAndPumpkins;

class IntegrationTestTaxableCountries implements TaxableCountries
{
    private static $countries = ['DE', 'FR'];
    
    public function getIterator()
    {
        return new \ArrayIterator(self::$countries);
    }

    public function getCountries()
    {
        return self::$countries;
    }
}
