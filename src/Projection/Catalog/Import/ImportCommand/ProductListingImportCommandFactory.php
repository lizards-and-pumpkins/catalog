<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand;

use LizardsAndPumpkins\Command;
use LizardsAndPumpkins\Factory;
use LizardsAndPumpkins\Product\ProductListingCriteria;

interface ProductListingImportCommandFactory extends Factory
{
    /**
     * @param ProductListingCriteria $productListingCriteria
     * @return Command[]
     */
    public function createProductListingImportCommands(ProductListingCriteria $productListingCriteria);
}
