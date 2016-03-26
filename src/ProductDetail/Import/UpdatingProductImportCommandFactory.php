<?php

namespace LizardsAndPumpkins\ProductDetail\Import;

use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\UpdateProductCommand;

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
