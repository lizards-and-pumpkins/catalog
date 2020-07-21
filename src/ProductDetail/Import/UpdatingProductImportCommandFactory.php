<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductDetail\Import;

use LizardsAndPumpkins\Import\Product\UpdateProductCommand;
use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Core\Factory\FactoryTrait;
use LizardsAndPumpkins\Import\Product\Product;

class UpdatingProductImportCommandFactory implements ProductImportCommandFactory
{
    use FactoryTrait;

    /**
     * @param Product $product
     * @return Command[]
     */
    public function createProductImportCommands(Product $product) : array
    {
        return [new UpdateProductCommand($product)];
    }
}
