<?php

namespace Brera\Product;

use Brera\Renderer\BlockRenderer;

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
