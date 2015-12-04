<?php


namespace LizardsAndPumpkins;

interface TaxableCountries extends \IteratorAggregate
{
    public function getCountries();
}
