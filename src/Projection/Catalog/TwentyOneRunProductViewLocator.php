<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductImage\ProductImageFileLocator;
use LizardsAndPumpkins\Projection\Catalog\PageTitle\TwentyOneRunProductPageTitle;

class TwentyOneRunProductViewLocator implements ProductViewLocator
{
    /**
     * @var ProductImageFileLocator
     */
    private $imageFileLocator;

    /**
     * @var TwentyOneRunProductPageTitle
     */
    private $pageTitle;

    public function __construct(ProductImageFileLocator $imageFileLocator, TwentyOneRunProductPageTitle $pageTitle)
    {
        $this->imageFileLocator = $imageFileLocator;
        $this->pageTitle = $pageTitle;
    }

    /**
     * @param Product $product
     * @return ProductView
     */
    public function createForProduct(Product $product)
    {
        if ($product instanceof ConfigurableProduct) {
            return new TwentyOneRunConfigurableProductView($this, $product, $this->pageTitle, $this->imageFileLocator);
        }

        return new TwentyOneRunSimpleProductView($product, $this->pageTitle, $this->imageFileLocator);
    }
}
