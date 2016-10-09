<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\View;

use LizardsAndPumpkins\Import\Product\Product;

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

    public function getOriginalProduct() : Product
    {
        return $this->product;
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
