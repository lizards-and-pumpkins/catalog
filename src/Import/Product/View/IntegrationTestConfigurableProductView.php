<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\View;

use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\Product;

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
    
    public function getOriginalProduct() : Product
    {
        return $this->configurableProduct;
    }

    final protected function getProductViewLocator() : ProductViewLocator
    {
        return $this->productViewLocator;
    }

    final protected function getProductImageFileLocator() : ProductImageFileLocator
    {
        return $this->productImageFileLocator;
    }

    public function getProductPageTitle() : string
    {
        return $this->getFirstValueOfAttribute('name');
    }
}
