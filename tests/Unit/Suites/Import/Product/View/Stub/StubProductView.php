<?php

namespace LizardsAndPumpkins\Import\Product\View\Stub;

use LizardsAndPumpkins\Import\Product\ProductDTO;
use LizardsAndPumpkins\Import\Product\View\AbstractProductView;
use LizardsAndPumpkins\Import\Product\View\ProductImageFileLocator;

class StubProductView extends AbstractProductView
{
    /**
     * @var ProductDTO
     */
    private $product;

    /**
     * @var ProductImageFileLocator
     */
    public $imageFileLocator;

    public function __construct(ProductDTO $product, ProductImageFileLocator $imageFileLocator)
    {
        $this->product = $product;
        $this->imageFileLocator = $imageFileLocator;
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
