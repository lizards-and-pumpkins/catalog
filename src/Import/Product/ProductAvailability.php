<?php

namespace LizardsAndPumpkins\Import\Product;

interface ProductAvailability
{
    /**
     * @param Product $product
     * @return bool
     */
    function isProductSalable(Product $product);
}
