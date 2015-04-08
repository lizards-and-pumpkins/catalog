<?php

namespace Brera\Product\Block;

use Brera\Product\ProductSource;
use Brera\Product\ProductAttributeNotFoundException;
use Brera\Renderer\Block;

class ProductDetailsPageBlock extends ProductBlock
{
    /**
     * @return \Brera\Product\ProductId
     */
    public function getProductId()
    {
        return $this->getProduct()->getId();
    }
}
