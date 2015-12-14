<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Product\Composite\AssociatedProductList;
use LizardsAndPumpkins\Product\Composite\ProductVariationAttributeList;

interface CompositeProduct extends Product
{
    /**
     * @return ProductVariationAttributeList
     */
    public function getVariationAttributes();

    /**
     * @return AssociatedProductList
     */
    public function getAssociatedProducts();
}
