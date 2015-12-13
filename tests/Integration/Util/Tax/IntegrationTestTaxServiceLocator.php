<?php

namespace LizardsAndPumpkins\Tax;

use LizardsAndPumpkins\Product\Tax\TaxService;
use LizardsAndPumpkins\Product\Tax\TaxServiceLocator;
use LizardsAndPumpkins\Product\Tax\TaxServiceLocatorOptions;

class IntegrationTestTaxServiceLocator implements TaxServiceLocator
{
    /**
     * @param TaxServiceLocatorOptions $options
     * @return TaxService
     */
    public function get(TaxServiceLocatorOptions $options)
    {
        return new IntegrationTestTaxService();
    }
}
