<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Command;

class UpdateProductListingCommand implements Command
{
    /**
     * @var ProductListingMetaInfo
     */
    private $productListingMetaInfoSource;

    public function __construct(ProductListingMetaInfo $productListingMetaInfoSource)
    {
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
