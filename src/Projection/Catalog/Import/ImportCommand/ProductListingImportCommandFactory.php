<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand;

use LizardsAndPumpkins\Command;
use LizardsAndPumpkins\Factory;
use LizardsAndPumpkins\Product\ProductListing;

interface ProductListingImportCommandFactory extends Factory
{
    /**
     * @param ProductListing $productListing
     * @return Command[]
     */
    public function createProductListingImportCommands(ProductListing $productListing);
}
