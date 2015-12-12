<?php

namespace LizardsAndPumpkins\Tax;

use LizardsAndPumpkins\Product\Tax\TaxServiceLocator;
use LizardsAndPumpkins\Product\Tax\TaxServiceLocatorOptions;

class IntegrationTestTaxServiceLocator implements TaxServiceLocator
{
    /**
     * @param \LizardsAndPumpkins\Product\Tax\TaxServiceLocatorOptions $options
     * @return \LizardsAndPumpkins\Product\Tax\TaxService
     */
    public function get(TaxServiceLocatorOptions $options)
    {
        return new IntegrationTestTaxService();
    }
}
