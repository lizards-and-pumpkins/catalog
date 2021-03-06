<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Tax;

use LizardsAndPumpkins\Import\Tax\TaxService;
use LizardsAndPumpkins\Import\Tax\TaxServiceLocator;

class IntegrationTestTaxServiceLocator implements TaxServiceLocator
{
    /**
     * @param mixed[] $options
     * @return TaxService
     */
    public function get(array $options) : TaxService
    {
        return new IntegrationTestTaxService();
    }
}
