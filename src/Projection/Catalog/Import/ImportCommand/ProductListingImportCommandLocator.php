<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand;

use LizardsAndPumpkins\Command;
use LizardsAndPumpkins\MasterFactory;
use LizardsAndPumpkins\Product\ProductListing;

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
     * @param ProductListing $productListing
     * @return Command[]
     */
    public function getProductListingImportCommands(ProductListing $productListing)
    {
        return $this->factory->createProductListingImportCommands($productListing);
    }
}
