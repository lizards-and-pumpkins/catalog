<?php

namespace Brera\Product;

use Brera\DomainEvent;

class ProductListingWasUpdatedDomainEvent implements DomainEvent
{
    /**
     * @var string
     */
    private $urlKey;

    /**
     * @var ProductListingMetaInfoSource
     */
    private $productListingMetaInfoSource;

    /**
     * @param string $urlKey
     * @param ProductListingMetaInfoSource $productListingMetaInfoSource
     */
    public function __construct($urlKey, ProductListingMetaInfoSource $productListingMetaInfoSource)
    {
        $this->urlKey = $urlKey;
        $this->productListingMetaInfoSource = $productListingMetaInfoSource;
    }

    /**
     * @return ProductListingMetaInfoSource
     */
    public function getProductListingMetaInfoSource()
    {
        return $this->productListingMetaInfoSource;
    }
}
