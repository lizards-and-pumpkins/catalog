<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\Composite;

use LizardsAndPumpkins\Import\Product\Product;

interface CompositeProduct extends Product
{
    public function getVariationAttributes() : ProductVariationAttributeList;

    public function getAssociatedProducts() : AssociatedProductList;
}
