<?php

namespace Brera\Product;

use Brera\Renderer\BlockRenderer;

class ProductDetailViewBlockRenderer extends BlockRenderer
{
    /**
     * @return string
     */
    final public function getLayoutHandle()
    {
        return 'product_detail_view';
    }
}
