<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductRelations;

use LizardsAndPumpkins\Import\Product\ProductId;

interface ProductRelations
{
    /**
     * @param ProductId $productId
     * @return ProductId[]
     */
    public function getById(ProductId $productId) : array;
}
