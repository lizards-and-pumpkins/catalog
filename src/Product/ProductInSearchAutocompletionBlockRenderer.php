<?php

namespace Brera\Product;

use Brera\Renderer\BlockRenderer;

class ProductInSearchAutocompletionBlockRenderer extends BlockRenderer
{
    public function getLayoutHandle()
    {
        return 'product_in_autocompletion';
    }
}
