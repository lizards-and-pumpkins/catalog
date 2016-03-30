<?php

namespace LizardsAndPumpkins\Import\Product\View;

use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;


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
    final protected function getProductViewLocator()
    {
        return $this->productViewLocator;
    }

    /**
     * @return ProductImageFileLocator
     */
    final protected function getProductImageFileLocator()
    {
        return $this->productImageFileLocator;
    }

    /**
     * @return string
     */
    public function getProductPageTitle()
    {
        $this->getFirstValueOfAttribute('name');
    }
}
