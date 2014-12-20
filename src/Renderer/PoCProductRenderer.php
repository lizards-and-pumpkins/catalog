<?php

namespace Brera\PoC\Renderer;

use Brera\PoC\Product\Product;

class PoCProductRenderer implements ProductRenderer
{
    /**
     * @param Product $product
     * @return string
     */
    public function render(Product $product)
    {
        return sprintf('<p>%s: %s</p>', $product->getId(), $product->getAttribute('name'));
    }
} 
