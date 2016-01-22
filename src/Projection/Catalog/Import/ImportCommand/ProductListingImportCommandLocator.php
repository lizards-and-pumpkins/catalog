<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand;

use LizardsAndPumpkins\Command;
use LizardsAndPumpkins\MasterFactory;
use LizardsAndPumpkins\Product\ProductListingCriteria;

class ProductListingImportCommandLocator
{
    /**
     * @var ProductListingImportCommandFactory
     */
    private $factory;

    public function __construct(MasterFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param ProductListingCriteria $productListingCriteria
     * @return Command[]
     */
    public function getProductListingImportCommands(ProductListingCriteria $productListingCriteria)
    {
        return $this->factory->createProductListingImportCommands($productListingCriteria);
    }
}
