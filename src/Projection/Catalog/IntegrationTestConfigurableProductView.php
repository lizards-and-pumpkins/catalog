<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Product\ProductImage\ProductImageFileLocator;

class IntegrationTestConfigurableProductView extends AbstractConfigurableProductView
{
    /**
     * @var ConfigurableProduct
     */
    private $configurableProduct;

    /**
     * @var ProductViewLocator
     */
    private $productViewLocator;

    /**
     * @var ProductImageFileLocator
     */
    private $productImageFileLocator;

    public function __construct(
        ConfigurableProduct $configurableProduct,
        ProductViewLocator $productViewLocator,
        ProductImageFileLocator $productImageFileLocator
    ) {
        $this->configurableProduct = $configurableProduct;
        $this->productViewLocator = $productViewLocator;
        $this->productImageFileLocator = $productImageFileLocator;
    }
    
    /**
     * @return ConfigurableProduct
     */
    public function getOriginalProduct()
    {
        return $this->configurableProduct;
    }

    /**
     * @return ProductViewLocator
     */
    protected function getProductViewLocator()
    {
        return $this->productViewLocator;
    }

    /**
     * @return ProductImageFileLocator
     */
    protected function getProductImageFileLocator()
    {
        return $this->productImageFileLocator;
    }
}
