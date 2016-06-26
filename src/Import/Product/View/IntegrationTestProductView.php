<?php

namespace LizardsAndPumpkins\Import\Product\View;

use LizardsAndPumpkins\Import\Product\ProductDTO;

class IntegrationTestProductView extends AbstractProductView
{
    /**
     * @var ProductDTO
     */
    private $product;

    /**
     * @var ProductImageFileLocator
     */
    private $productImageFileLocator;

    public function __construct(ProductDTO $product, ProductImageFileLocator $productImageFileLocator)
    {
        $this->product = $product;
        $this->productImageFileLocator = $productImageFileLocator;
    }

    /**
     * @return ProductDTO
     */
    public function getOriginalProduct()
    {
        return $this->product;
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
        return $this->getFirstValueOfAttribute('name');
    }
}
