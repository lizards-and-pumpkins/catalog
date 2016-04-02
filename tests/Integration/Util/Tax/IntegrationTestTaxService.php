<?php

namespace LizardsAndPumpkins\Tax;

use LizardsAndPumpkins\Import\Price\Price;
use LizardsAndPumpkins\Import\Tax\TaxService;

class IntegrationTestTaxService implements TaxService
{
    /**
     * @param Price $price
     * @return Price
     */
    public function applyTo(Price $price)
    {
        return $price;
    }
}
