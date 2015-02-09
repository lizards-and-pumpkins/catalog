<?php

namespace Brera\Product\Block;

use Brera\Product\ProductSource;
use Brera\Renderer\Block;

class ProductDetailsPage extends Block
{
    /**
     * @return ProductSource
     */
    public function getProduct()
    {
        return $this->getDataObject();
    }
}
