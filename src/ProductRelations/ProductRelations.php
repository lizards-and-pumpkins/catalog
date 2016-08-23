<?php

namespace LizardsAndPumpkins\ProductRelations;

use LizardsAndPumpkins\Import\Product\ProductId;

interface ProductRelations
{
    /**
     * @param ProductId $productId
     * @return ProductId[]
     */
    public function getById(ProductId $productId);
}
