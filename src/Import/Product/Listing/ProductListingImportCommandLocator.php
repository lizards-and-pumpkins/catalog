<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\Listing;

use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\ProductListing\Import\ProductListingImportCommandFactory;
use LizardsAndPumpkins\Core\Factory\MasterFactory;
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
     * @return Command[]
     */
    public function getProductListingImportCommands(ProductListing $productListing) : array
    {
        return $this->factory->createProductListingImportCommands($productListing);
    }
}
