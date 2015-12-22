<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Product\Composite\AssociatedProductList;
use LizardsAndPumpkins\Product\Composite\ProductVariationAttributeList;

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
