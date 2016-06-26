<?php

namespace LizardsAndPumpkins\Import\Product\View;

use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\ProductDTO;

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
     * @param ProductDTO $product
     * @return ProductView
     */
    public function createForProduct(ProductDTO $product)
    {
        return $product instanceof ConfigurableProduct ?
            new IntegrationTestConfigurableProductView($product, $this, $this->productImageFileLocator) :
            new IntegrationTestProductView($product, $this->productImageFileLocator);
    }
}
