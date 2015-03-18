<?php

namespace Brera\Product;

use Brera\Renderer\BlockRenderer;

class ProductInListingBlockRenderer extends BlockRenderer
{
    /**
     * @return string
     */
    protected function getLayoutHandle()
    {
        return 'product_in_listing';
    }

    /**
     * @return \Brera\Product\Product
     */
    public function getProduct()
    {
        return $this->getDataObject();
    }
}
