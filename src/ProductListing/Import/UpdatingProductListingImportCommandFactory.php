<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;
use LizardsAndPumpkins\ProductListing\AddProductListingCommand;

class UpdatingProductListingImportCommandFactory implements ProductListingImportCommandFactory
{
    use FactoryTrait;

    /**
     * @param ProductListing $productListing
     * @return array[]
     */
    public function createProductListingImportCommands(ProductListing $productListing): array
    {
        $payload = json_encode(['listing' => $productListing->serialize()]);
        return [['name' => 'add_product_listing', 'payload' => $payload]];
    }
}
