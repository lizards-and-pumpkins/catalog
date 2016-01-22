<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand;

use LizardsAndPumpkins\Command;
use LizardsAndPumpkins\FactoryTrait;
use LizardsAndPumpkins\Product\AddProductListingCommand;
use LizardsAndPumpkins\Product\ProductListingCriteria;

class UpdatingProductListingImportCommandFactory implements ProductListingImportCommandFactory
{
    use FactoryTrait;

    /**
     * @param ProductListingCriteria $productListingCriteria
     * @return Command[]
     */
    public function createProductListingImportCommands(ProductListingCriteria $productListingCriteria)
    {
        return [new AddProductListingCommand($productListingCriteria)];
    }
}
