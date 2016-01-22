<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand;

use LizardsAndPumpkins\Command;
use LizardsAndPumpkins\MasterFactory;
use LizardsAndPumpkins\Product\Product;

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
