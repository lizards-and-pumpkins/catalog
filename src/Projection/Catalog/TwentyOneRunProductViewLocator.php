<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductImage\ProductImageFileLocator;

class TwentyOneRunProductViewLocator implements ProductViewLocator
{
    /**
     * @var ProductImageFileLocator
     */
    private $imageFileLocator;

    public function __construct(ProductImageFileLocator $imageFileLocator)
    {
        $this->imageFileLocator = $imageFileLocator;
    }

    /**
     * @param Product $product
     * @return ProductView
     */
    public function createForProduct(Product $product)
    {
        if ($product instanceof ConfigurableProduct) {
            return new TwentyOneRunConfigurableProductView($this, $product, $this->imageFileLocator);
        }

        return new TwentyOneRunSimpleProductView($product, $this->imageFileLocator);
    }
}
