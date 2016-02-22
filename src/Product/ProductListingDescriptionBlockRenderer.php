<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Renderer\BlockRenderer;

class ProductListingDescriptionBlockRenderer extends BlockRenderer
{
    /**
     * @return string
     */
    public function getLayoutHandle()
    {
        return 'product_listing_description';
    }
}
