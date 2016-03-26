<?php

namespace LizardsAndPumpkins\Import\Tax;

interface TaxableCountries extends \IteratorAggregate
{
    /**
     * @return string[]
     */
    public function getCountries();
}
