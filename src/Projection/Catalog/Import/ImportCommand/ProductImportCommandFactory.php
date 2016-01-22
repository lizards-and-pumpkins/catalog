<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand;

use LizardsAndPumpkins\Command;
use LizardsAndPumpkins\Factory;
use LizardsAndPumpkins\Product\Product;

interface ProductImportCommandFactory extends Factory
{
    /**
     * @param Product $product
     * @return Command[]
     */
    public function createProductImportCommands(Product $product);
}
