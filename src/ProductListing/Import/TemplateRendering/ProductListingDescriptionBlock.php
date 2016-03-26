<?php

namespace LizardsAndPumpkins\ProductListing\Import\TemplateRendering;

use LizardsAndPumpkins\ProductListing\Import\ProductListing;
use LizardsAndPumpkins\Import\ContentBlock\Block;

class ProductListingDescriptionBlock extends Block
{
    /**
     * @return ProductListing
     */
    private function getProductListing()
    {
        return $this->getDataObject();
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
