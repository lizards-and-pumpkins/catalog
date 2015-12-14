<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Product\Product;

interface ProductView extends Product
{
    /**
     * @return Product
     */
    public function getOriginalProduct();
}
