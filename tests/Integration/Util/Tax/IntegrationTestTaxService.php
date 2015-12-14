<?php

namespace LizardsAndPumpkins\Tax;

use LizardsAndPumpkins\Product\Price;
use LizardsAndPumpkins\Product\Tax\TaxService;

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
