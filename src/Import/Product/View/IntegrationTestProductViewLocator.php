<?php

namespace LizardsAndPumpkins\Import\Product\View;

use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\View\ProductImageFileLocator;

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
