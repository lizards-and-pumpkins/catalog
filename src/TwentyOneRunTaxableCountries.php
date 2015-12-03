<?php

namespace LizardsAndPumpkins;

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
    
    public function getCountries()
    {
        return self::$countries;
    }
    
    public function getIterator()
    {
        return new \ArrayIterator(self::$countries);
    }
}
