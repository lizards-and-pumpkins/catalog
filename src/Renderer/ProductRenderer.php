<?php

namespace Brera\Renderer;

use Brera\Product\Product;

interface ProductRenderer
{
    /**
     * @param Product $product
     * @return string
     */
    public function render(Product $product);
}
