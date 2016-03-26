<?php

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;

interface ProductListingImportCommandFactory extends Factory
{
    /**
     * @param ProductListing $productListing
     * @return Command[]
     */
    public function createProductListingImportCommands(ProductListing $productListing);
}
