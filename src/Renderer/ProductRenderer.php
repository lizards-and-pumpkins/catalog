<?php

namespace Brera\PoC;

interface ProductRenderer
{
    /**
     * @param Product $product
     * @return string
     */
    public function render(Product $product);
}