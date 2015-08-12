<?php

namespace Brera\Product;

use Brera\Renderer\BlockRenderer;

class ProductInSearchAutocompletionBlockRenderer extends BlockRenderer
{
    /**
     * @return string
     */
    public function getLayoutHandle()
    {
        return 'product_in_autocompletion';
    }
}
