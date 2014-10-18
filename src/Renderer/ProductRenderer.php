<?php

namespace Brera\PoC\Renderer;

use Brera\PoC\Product\Product;

interface ProductRenderer
{
    /**
     * @param Product $product
     * @return string
     */
    public function render(Product $product);
}
