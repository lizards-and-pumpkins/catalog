<?php

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\ProductListing\AddProductListingCommand;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;

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
