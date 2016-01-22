<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand;

use LizardsAndPumpkins\Command;
use LizardsAndPumpkins\FactoryTrait;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\UpdateProductCommand;

class UpdatingProductImportCommandFactory implements ProductImportCommandFactory
{
    use FactoryTrait;
    
    /**
     * @param Product $product
     * @return Command[]
     */
    public function createProductImportCommands(Product $product)
    {
        return [new UpdateProductCommand($product)];
    }
}
