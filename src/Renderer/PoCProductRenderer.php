<?php


namespace Brera\PoC;


class PoCProductRenderer implements ProductRenderer
{
    public function render(Product $product)
    {
        return sprintf('<p>%s: %s</p>', $product->getId(), $product->getName());
    }
} 