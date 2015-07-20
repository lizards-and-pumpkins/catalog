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
     * @var ProductListingSource
     */
    private $productListingSource;

    /**
     * @param string $urlKey
     */
    public function __construct($urlKey, ProductListingSource $productListingSource)
    {
        $this->urlKey = $urlKey;
        $this->productListingSource = $productListingSource;
    }

    /**
     * @return ProductListingSource
     */
    public function getProductListingSource()
    {
        return $this->productListingSource;
    }
}
