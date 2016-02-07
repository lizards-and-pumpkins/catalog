<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductImage\ProductImageFileLocator;

class IntegrationTestProductView extends AbstractProductView
{
    /**
     * @var Product
     */
    private $product;

    /**
     * @var ProductImageFileLocator
     */
    private $productImageFileLocator;

    public function __construct(Product $product, ProductImageFileLocator $productImageFileLocator)
    {
        $this->product = $product;
        $this->productImageFileLocator = $productImageFileLocator;
    }

    /**
     * @return Product
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
        $this->getFirstValueOfAttribute('name');
    }
}
