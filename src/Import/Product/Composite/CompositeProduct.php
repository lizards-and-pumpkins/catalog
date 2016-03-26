<?php

namespace LizardsAndPumpkins\Import\Product\Composite;

use LizardsAndPumpkins\Import\Product\Composite\AssociatedProductList;
use LizardsAndPumpkins\Import\Product\Composite\ProductVariationAttributeList;
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
