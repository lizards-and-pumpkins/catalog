<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductImage\ProductImageFileLocator;

class StubProductView extends AbstractProductView
{
    /**
     * @var Product
     */
    private $product;

    /**
     * @var ProductImageFileLocator
     */
    public $imageFileLocator;

    public function __construct(Product $product, ProductImageFileLocator $imageFileLocator)
    {
        $this->product = $product;
        $this->imageFileLocator = $imageFileLocator;
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
        return $this->imageFileLocator;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductPageTitle()
    {
        // Intentionally left empty
    }
}
