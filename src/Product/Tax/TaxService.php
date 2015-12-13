<?php

namespace LizardsAndPumpkins\Product\Tax;

use LizardsAndPumpkins\Product\Price;

interface TaxService
{
    /**
     * @param Price $price
     * @return Price
     */
    public function applyTo(Price $price);
}
