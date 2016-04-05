<?php

namespace LizardsAndPumpkins\Import\Product\View\Stub;

use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\View\AbstractProductView;
use LizardsAndPumpkins\Import\Product\View\ProductImageFileLocator;

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
     * @return string
     */
    public function getProductPageTitle()
    {
        return $this->getFirstValueOfAttribute('name');
    }
}
