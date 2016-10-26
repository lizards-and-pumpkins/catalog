<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductDetail\Import;

use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Util\Factory\Factory;
use LizardsAndPumpkins\Import\Product\Product;

interface ProductImportCommandFactory extends Factory
{
    /**
     * @param Product $product
     * @return Command[]
     */
    public function createProductImportCommands(Product $product) : array;
}
