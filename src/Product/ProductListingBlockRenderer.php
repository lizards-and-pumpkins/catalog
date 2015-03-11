<?php

namespace Brera\Product;

use Brera\Renderer\BlockRenderer;

class ProductListingBlockRenderer extends BlockRenderer
{
    /**
     * @return string
     */
    protected function getLayoutHandle()
    {
        return 'product_listing';
    }
}
