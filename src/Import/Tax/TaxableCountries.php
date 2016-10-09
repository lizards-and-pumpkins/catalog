<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Tax;

interface TaxableCountries extends \IteratorAggregate
{
    /**
     * @return string[]
     */
    public function getCountries() : array;
}
