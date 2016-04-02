<?php

namespace LizardsAndPumpkins\Import\Tax;

class TwentyOneRunTaxableCountries implements TaxableCountries
{
    private static $countries = [
        'DE',
        'AT',
        'DK',
        'FR',
        'ES',
        'FI',
        'NL',
        'SE',
        'LU',
        'IT',
        'BE',
    ];

    /**
     * @return string[]
     */
    public function getCountries()
    {
        return self::$countries;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator(self::$countries);
    }
}
