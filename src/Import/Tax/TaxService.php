<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Tax;

use LizardsAndPumpkins\Import\Price\Price;

interface TaxService
{
    public function applyTo(Price $price) : Price;
}
