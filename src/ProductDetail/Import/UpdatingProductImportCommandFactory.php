<?php

namespace LizardsAndPumpkins\ProductDetail\Import;

use LizardsAndPumpkins\Util\Factory\FactoryTrait;
use LizardsAndPumpkins\Import\Product\Product;

class UpdatingProductImportCommandFactory implements ProductImportCommandFactory
{
    use FactoryTrait;

    /**
     * @param Product $product
     * @return array[]
     */
    public function createProductImportCommands(Product $product)
    {
        $payload = json_encode(['id' => (string) $product->getId(), 'product' => $product]);
        return [['name' => 'update_product', 'payload' => $payload]];
    }
}
