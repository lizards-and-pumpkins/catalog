<?php

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\Util\Factory\Factory;

interface ProductListingImportCommandFactory extends Factory
{
    /**
     * @param ProductListing $productListing
     * @return array[]
     */
    public function createProductListingImportCommands(ProductListing $productListing);
}
