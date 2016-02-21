<?php

namespace LizardsAndPumpkins\Product\Block;

use LizardsAndPumpkins\Product\ProductListing;
use LizardsAndPumpkins\Renderer\Block;

class ProductListingDescriptionBlock extends Block
{
    /**
     * @return ProductListing
     */
    private function getProductListing()
    {
        return $productListing = $this->getDataObject();
    }

    /**
     * @return string
     */
    public function getListingDescription()
    {
        $productListing = $this->getProductListing();
        return $productListing->hasAttribute('description') ?
            (string)$productListing->getAttributeValueByCode('description') :
            '';
    }

    /**
     * @return string
     */
    public function getListingTitle()
    {
        $productListing = $this->getProductListing();
        return $productListing->hasAttribute('title') ?
            (string)$productListing->getAttributeValueByCode('title') :
            '';
    }
}
