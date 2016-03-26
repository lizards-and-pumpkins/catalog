<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\ProductDetail\Import\ProductImportCommandFactory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use LizardsAndPumpkins\Import\Product\Product;

class ProductImportCommandLocator
{
    /**
     * @var ProductImportCommandFactory
     */
    private $factory;

    public function __construct(MasterFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param Product $product
     * @return Command[]
     */
    public function getProductImportCommands(Product $product)
    {
        return $this->factory->createProductImportCommands($product);
    }
}
