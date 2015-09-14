<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Renderer\BlockRenderer;

class ProductListingBlockRenderer extends BlockRenderer
{
    /**
     * @return string
     */
    final public function getLayoutHandle()
    {
        return 'product_listing';
    }
}
