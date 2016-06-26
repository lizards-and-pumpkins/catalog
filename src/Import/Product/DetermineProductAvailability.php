<?php

namespace LizardsAndPumpkins\Import\Product;

interface DetermineProductAvailability
{
    /**
     * @param ProductDTO $product
     * @return bool
     */
    public function forProduct(ProductDTO $product);
}
