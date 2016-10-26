<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\View;

use LizardsAndPumpkins\Import\Product\Composite\ProductVariationAttributeList;

interface CompositeProductView extends ProductView
{
    public function getVariationAttributes() : ProductVariationAttributeList;

    /**
     * @return ProductView[]
     */
    public function getAssociatedProducts() : array;
}
