<?php

namespace LizardsAndPumpkins\ProductDetail\Import;

use LizardsAndPumpkins\Import\Product\UpdateProductCommand;
use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;
use LizardsAndPumpkins\Import\Product\ProductDTO;

class UpdatingProductImportCommandFactory implements ProductImportCommandFactory
{
    use FactoryTrait;

    /**
     * @param ProductDTO $product
     * @return Command[]
     */
    public function createProductImportCommands(ProductDTO $product)
    {
        return [new UpdateProductCommand($product)];
    }
}
