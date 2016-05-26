<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ProductDetail\Import;

use LizardsAndPumpkins\Util\Factory\FactoryTrait;
use LizardsAndPumpkins\Import\Product\Product;

class UpdatingProductImportCommandFactory implements ProductImportCommandFactory
{
    use FactoryTrait;

    public function createProductImportCommands(Product $product): array
    {
        $payload = json_encode(['id' => (string) $product->getId(), 'product' => $product]);
        return [['name' => 'update_product', 'payload' => $payload]];
    }
}
