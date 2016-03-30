<?php

namespace LizardsAndPumpkins\Import\Product\View;

use LizardsAndPumpkins\Import\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Import\Product\ProductAttribute;

use LizardsAndPumpkins\ProductDetail\Import\View\TwentyOneRunProductPageTitle;

class TwentyOneRunConfigurableProductView extends AbstractConfigurableProductView implements CompositeProductView
{
    const MAX_PURCHASABLE_QTY = 5;

    /**
     * @var ProductViewLocator
     */
    private $productViewLocator;

    /**
     * @var ConfigurableProduct
     */
    private $product;

    /**
     * @var TwentyOneRunProductPageTitle
     */
    private $pageTitle;

    /**
     * @var ProductImageFileLocator
     */
    private $productImageFileLocator;

    public function __construct(
        ProductViewLocator $productViewLocator,
        ConfigurableProduct $product,
        TwentyOneRunProductPageTitle $pageTitle,
        ProductImageFileLocator $productImageFileLocator
    ) {
        $this->productViewLocator = $productViewLocator;
        $this->product = $product;
        $this->pageTitle = $pageTitle;
        $this->productImageFileLocator = $productImageFileLocator;
    }

    /**
     * {@inheritdoc}
     */
    public function getOriginalProduct()
    {
        return $this->product;
    }

    /**
     * @param ProductAttribute $attribute
     * @return bool
     */
    final protected function isAttributePublic(ProductAttribute $attribute)
    {
        return in_array($attribute->getCode(), ['backorders']) ?
            false :
            parent::isAttributePublic($attribute);
    }

    /**
     * @return ProductImageFileLocator
     */
    final protected function getProductImageFileLocator()
    {
        return $this->productImageFileLocator;
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
    final public function getProductPageTitle()
    {
        return $this->pageTitle->forProductView($this);
    }
}
