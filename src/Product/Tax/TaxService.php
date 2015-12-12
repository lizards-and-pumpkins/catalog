<?php


namespace LizardsAndPumpkins\Product\Tax;

interface TaxService
{
    /**
     * @param int $price
     * @return int
     */
    public function apply($price);
}
