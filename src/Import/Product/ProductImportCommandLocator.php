<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\ProductDetail\Import\ProductImportCommandFactory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

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
     * @param ProductDTO $product
     * @return Command[]
     */
    public function getProductImportCommands(ProductDTO $product)
    {
        return $this->factory->createProductImportCommands($product);
    }
}
