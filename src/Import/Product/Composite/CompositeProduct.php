<?php

namespace LizardsAndPumpkins\Import\Product\Composite;

use LizardsAndPumpkins\Import\Product\Product;

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
