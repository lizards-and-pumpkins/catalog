<?php

namespace LizardsAndPumpkins\Import\Product;

interface ProductAvailability
{
    /**
     * @param Product $product
     * @return bool
     */
    public function isProductSalable(Product $product);
}
