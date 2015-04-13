<?php

namespace Brera\Product\Block;

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
