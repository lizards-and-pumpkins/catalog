<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductDetail\Import;

use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Core\Factory\Factory;
use LizardsAndPumpkins\Import\Product\Product;

interface ProductImportCommandFactory extends Factory
{
    /**
     * @param Product $product
     * @return Command[]
     */
    public function createProductImportCommands(Product $product) : array;
}
