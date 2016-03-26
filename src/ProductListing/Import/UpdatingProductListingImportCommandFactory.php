<?php

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;
use LizardsAndPumpkins\ProductListing\AddProductListingCommand;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;

class UpdatingProductListingImportCommandFactory implements ProductListingImportCommandFactory
{
    use FactoryTrait;

    /**
     * @param ProductListing $productListing
     * @return Command[]
     */
    public function createProductListingImportCommands(ProductListing $productListing)
    {
        return [new AddProductListingCommand($productListing)];
    }
}
