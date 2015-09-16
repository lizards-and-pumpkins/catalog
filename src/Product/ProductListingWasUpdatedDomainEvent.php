<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DomainEvent;

class ProductListingWasUpdatedDomainEvent implements DomainEvent
{
    /**
     * @var string
     */
    private $urlKey;

    /**
     * @var ProductListingMetaInfo
     */
    private $productListingMetaInfoSource;

    /**
     * @param string $urlKey
     * @param ProductListingMetaInfo $productListingMetaInfoSource
     */
    public function __construct($urlKey, ProductListingMetaInfo $productListingMetaInfoSource)
    {
        $this->urlKey = $urlKey;
        $this->productListingMetaInfoSource = $productListingMetaInfoSource;
    }

    /**
     * @return ProductListingMetaInfo
     */
    public function getProductListingMetaInfoSource()
    {
        return $this->productListingMetaInfoSource;
    }
}
