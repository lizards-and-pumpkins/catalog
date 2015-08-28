<?php

namespace Brera\Product;

use Brera\Renderer\BlockRenderer;

class ProductInSearchAutosuggestionBlockRenderer extends BlockRenderer
{
    /**
     * @return string
     */
    public function getLayoutHandle()
    {
        return 'product_in_autosuggestion';
    }
}
