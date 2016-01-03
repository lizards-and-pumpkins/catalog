<?php

namespace LizardsAndPumpkins\Tax;

use LizardsAndPumpkins\Product\Tax\TaxService;
use LizardsAndPumpkins\Product\Tax\TaxServiceLocator;

class IntegrationTestTaxServiceLocator implements TaxServiceLocator
{
    /**
     * @param mixed[] $options
     * @return TaxService
     */
    public function get(array $options)
    {
        return new IntegrationTestTaxService();
    }
}
