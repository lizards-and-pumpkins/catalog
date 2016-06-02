<?php

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Util\Factory\Factory;

interface ProductListingImportCommandFactory extends Factory
{
    /**
     * @param ProductListing $productListing
     * @return Command[]
     */
    public function createProductListingImportCommands(ProductListing $productListing);
}
