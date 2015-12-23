<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductImage\ProductImageFileLocator;

class IntegrationTestProductViewLocator implements ProductViewLocator
{
    /**
     * @var ProductImageFileLocator
     */
    private $productImageFileLocator;

    public function __construct(ProductImageFileLocator $productImageFileLocator)
    {
        $this->productImageFileLocator = $productImageFileLocator;
    }
    
    /**
     * @param Product $product
     * @return ProductView
     */
    public function createForProduct(Product $product)
    {
        return new IntegrationTestProductView($product, $this->productImageFileLocator);
    }
}
