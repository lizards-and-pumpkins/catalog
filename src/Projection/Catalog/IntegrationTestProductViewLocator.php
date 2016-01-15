<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Product\Composite\ConfigurableProduct;
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
        return $product instanceof ConfigurableProduct ?
            new IntegrationTestConfigurableProductView($product, $this, $this->productImageFileLocator) :
            new IntegrationTestProductView($product, $this->productImageFileLocator);
    }
}
