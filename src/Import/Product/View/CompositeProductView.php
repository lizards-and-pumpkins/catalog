<?php

namespace LizardsAndPumpkins\Import\Product\View;

use LizardsAndPumpkins\Import\Product\Composite\ProductVariationAttributeList;

interface CompositeProductView extends ProductView
{
    /**
     * @return ProductVariationAttributeList
     */
    public function getVariationAttributes();

    /**
     * @return ProductView[]
     */
    public function getAssociatedProducts();
}
