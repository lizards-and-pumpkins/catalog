<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Product\CompositeProduct;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductImage\ProductImageFileLocator;

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
}
