<?php

namespace LizardsAndPumpkins\Import\Tax;

interface TaxServiceLocator
{
    const OPTION_COUNTRY = 'country';
    const OPTION_WEBSITE = 'website';
    const OPTION_PRODUCT_TAX_CLASS = 'product_tax_class';
    
    /**
     * @param mixed[] $options
     * @return TaxService
     */
    public function get(array $options);
}
