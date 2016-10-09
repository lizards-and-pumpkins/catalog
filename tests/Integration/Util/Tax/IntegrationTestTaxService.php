<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Tax;

use LizardsAndPumpkins\Import\Price\Price;
use LizardsAndPumpkins\Import\Tax\TaxService;

class IntegrationTestTaxService implements TaxService
{
    public function applyTo(Price $price) : Price
    {
        return $price;
    }
}
