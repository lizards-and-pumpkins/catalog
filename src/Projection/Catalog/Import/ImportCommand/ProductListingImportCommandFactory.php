<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand;

use LizardsAndPumpkins\Command;
use LizardsAndPumpkins\Product\ProductListingCriteria;

interface ProductListingImportCommandFactory
{
    /**
     * @param ProductListingCriteria $productListingCriteria
     * @return Command[]
     */
    public function createProductListingImportCommands(ProductListingCriteria $productListingCriteria);
}
