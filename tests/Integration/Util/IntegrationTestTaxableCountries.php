<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Import\Tax\TaxableCountries;

class IntegrationTestTaxableCountries implements TaxableCountries
{
    private static $countries = ['DE', 'FR'];

    public function getIterator() : \ArrayIterator
    {
        return new \ArrayIterator(self::$countries);
    }

    /**
     * @return string[]
     */
    public function getCountries() : array
    {
        return self::$countries;
    }
}
