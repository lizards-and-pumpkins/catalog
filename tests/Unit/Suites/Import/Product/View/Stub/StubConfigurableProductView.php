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
    
    /**
     * @return ProductImageFileLocator
     */
    final protected function getProductImageFileLocator()
    {
        return $this->imageFileLocator;
    }

    /**
     * @return Product
     */
    public function getOriginalProduct()
    {
        return $this->compositeProduct;
    }

    /**
     * @return ProductViewLocator
     */
    final protected function getProductViewLocator()
    {
        return $this->productViewLocator;
    }

    /**
     * @return string
     */
    public function getProductPageTitle()
    {
        return $this->getFirstValueOfAttribute('name');
    }
}
