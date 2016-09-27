<?php

namespace LizardsAndPumpkins\Import\Product\View\Stub;

use LizardsAndPumpkins\Import\Product\Composite\CompositeProduct;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\View\AbstractConfigurableProductView;
use LizardsAndPumpkins\Import\Product\View\ProductImageFileLocator;
use LizardsAndPumpkins\Import\Product\View\ProductViewLocator;

class StubConfigurableProductView extends AbstractConfigurableProductView
{
    /**
     * @var CompositeProduct
     */
    private $compositeProduct;

    /**
     * @var ProductImageFileLocator
     */
    private $imageFileLocator;

    /**
     * @var ProductViewLocator
     */
    private $productViewLocator;

    public function __construct(
        CompositeProduct $product,
        ProductImageFileLocator $imageFileLocator,
        ProductViewLocator $productViewLocator
    ) {
        $this->compositeProduct = $product;
        $this->imageFileLocator = $imageFileLocator;
        $this->productViewLocator = $productViewLocator;
    }
    
    final protected function getProductImageFileLocator() : ProductImageFileLocator
    {
        return $this->imageFileLocator;
    }

    public function getOriginalProduct() : Product
    {
        return $this->compositeProduct;
    }

    final protected function getProductViewLocator() : ProductViewLocator
    {
        return $this->productViewLocator;
    }

    public function getProductPageTitle() : string
    {
        return $this->getFirstValueOfAttribute('name');
    }
}
