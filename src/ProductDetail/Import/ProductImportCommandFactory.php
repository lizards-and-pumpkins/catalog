<?php

namespace LizardsAndPumpkins\ProductDetail\Import;

use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Import\Product\ProductDTO;

interface ProductImportCommandFactory extends Factory
{
    /**
     * @param ProductDTO $product
     * @return Command[]
     */
    public function createProductImportCommands(ProductDTO $product);
}
