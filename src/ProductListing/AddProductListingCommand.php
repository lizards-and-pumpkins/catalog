<?php

namespace LizardsAndPumpkins\ProductListing;

use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;

class AddProductListingCommand implements Command
{
    /**
     * @var ProductListing
     */
    private $productListing;

    public function __construct(ProductListing $productListing)
    {
        $this->productListing = $productListing;
    }

    /**
     * @return ProductListing
     */
    public function getProductListing()
    {
        return $this->productListing;
    }
}
