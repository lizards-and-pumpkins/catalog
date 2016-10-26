<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import\TemplateRendering;

use LizardsAndPumpkins\Import\TemplateRendering\Block;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;

class ProductListingDescriptionBlock extends Block
{
    private function getProductListing() : ProductListing
    {
        return $this->getDataObject();
    }

    public function getListingDescription() : string
    {
        $productListing = $this->getProductListing();
        return $productListing->hasAttribute('description') ?
            (string)$productListing->getAttributeValueByCode('description') :
            '';
    }

    public function getListingTitle() : string
    {
        $productListing = $this->getProductListing();
        return $productListing->hasAttribute('title') ?
            (string)$productListing->getAttributeValueByCode('title') :
            '';
    }
}
