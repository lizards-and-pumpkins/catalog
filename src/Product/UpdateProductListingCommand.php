<?php

namespace Brera\Product;

use Brera\Command;

class UpdateProductListingCommand implements Command
{
    /**
     * @var ProductListingSource
     */
    private $productListingSource;

    public function __construct(ProductListingSource $productListingSource)
    {
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
