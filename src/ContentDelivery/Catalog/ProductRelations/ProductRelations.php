<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog\ProductRelations;

use LizardsAndPumpkins\Product\ProductId;

interface ProductRelations
{
    /**
     * @param ProductId $productId
     * @return ProductId[]
     */
    public function getById(ProductId $productId);
}
