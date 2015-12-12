<?php


namespace LizardsAndPumpkins\Product\Tax;

interface TaxServiceLocator
{
    /**
     * @param TaxServiceLocatorOptions $options
     * @return TaxService
     */
    public function get(TaxServiceLocatorOptions $options);
}
