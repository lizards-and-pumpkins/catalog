<?php

namespace LizardsAndPumpkins;

interface TaxableCountries extends \IteratorAggregate
{
    /**
     * @return string[]
     */
    public function getCountries();
}
