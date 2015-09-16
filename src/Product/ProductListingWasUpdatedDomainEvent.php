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
    private $productListingMetaInfo;

    /**
     * @param string $urlKey
     * @param ProductListingMetaInfo $productListingMetaInfo
     */
    public function __construct($urlKey, ProductListingMetaInfo $productListingMetaInfo)
    {
        $this->urlKey = $urlKey;
        $this->productListingMetaInfo = $productListingMetaInfo;
    }

    /**
     * @return ProductListingMetaInfo
     */
    public function getProductListingMetaInfo()
    {
        return $this->productListingMetaInfo;
    }
}
