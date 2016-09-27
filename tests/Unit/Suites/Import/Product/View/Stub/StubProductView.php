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

    public function getOriginalProduct() : Product
    {
        return $this->product;
    }

    final protected function getProductImageFileLocator() : ProductImageFileLocator
    {
        return $this->imageFileLocator;
    }

    public function getProductPageTitle() : string
    {
        return $this->getFirstValueOfAttribute('name');
    }
}
