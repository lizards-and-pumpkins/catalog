<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\View;

use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\Product;

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
    
    public function createForProduct(Product $product) : ProductView
    {
        return $product instanceof ConfigurableProduct ?
            new IntegrationTestConfigurableProductView($product, $this, $this->productImageFileLocator) :
            new IntegrationTestProductView($product, $this->productImageFileLocator);
    }
}
