<?php

namespace LizardsAndPumpkins\Import\Product\Listing;

use LizardsAndPumpkins\ProductListing\Import\ProductListingImportCommandFactory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;

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
     * @return array[]
     */
    public function getProductListingImportCommands(ProductListing $productListing)
    {
        return $this->factory->createProductListingImportCommands($productListing);
    }
}
