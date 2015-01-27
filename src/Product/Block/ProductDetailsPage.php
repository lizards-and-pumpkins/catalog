<?php

namespace Brera\Product\Block;

use Brera\Product\Product;
use Brera\Renderer\Block;

class ProductDetailsPage extends Block
{
    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->getDataObject();
    }
}
