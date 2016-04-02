<?php

namespace LizardsAndPumpkins\Import\Tax;

use LizardsAndPumpkins\Import\Price\Price;

interface TaxService
{
    /**
     * @param Price $price
     * @return Price
     */
    public function applyTo(Price $price);
}
