<?php

namespace LizardsAndPumpkins\Import\Product\Composite;

use LizardsAndPumpkins\Import\Product\ProductDTO;

interface CompositeProductDTO extends ProductDTO
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
