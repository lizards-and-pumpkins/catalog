<?php

namespace Brera\Product;

use Brera\Renderer\BlockRenderer;

class ProductSearchAutosuggestionBlockRenderer extends BlockRenderer
{
    /**
     * @return string
     */
    public function getLayoutHandle()
    {
        return 'product_search_autosuggestion';
    }
}
