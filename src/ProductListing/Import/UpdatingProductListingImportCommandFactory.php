<?php

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\Util\Factory\FactoryTrait;

class UpdatingProductListingImportCommandFactory implements ProductListingImportCommandFactory
{
    use FactoryTrait;

    /**
     * @param ProductListing $productListing
     * @return array[]
     */
    public function createProductListingImportCommands(ProductListing $productListing)
    {
        $payload = json_encode(['listing' => $productListing->serialize()]);
        return [['name' => 'add_product_listing', 'payload' => $payload]];
    }
}
