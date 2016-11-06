<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductRelations;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Product\ProductId;

interface ProductRelations
{
    /**
     * @param Context $context
     * @param ProductId $productId
     * @return ProductId[]
     */
    public function getById(Context $context, ProductId $productId) : array;
}
