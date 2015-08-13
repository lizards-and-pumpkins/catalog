<?php

namespace Brera\Product;

use Brera\Command;

class UpdateProductListingCommand implements Command
{
    /**
     * @var ProductListingMetaInfoSource
     */
    private $productListingMetaInfoSource;

    public function __construct(ProductListingMetaInfoSource $productListingMetaInfoSource)
    {
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
