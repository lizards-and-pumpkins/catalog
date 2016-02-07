<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductAttribute;
use LizardsAndPumpkins\Product\ProductImage\ProductImageFileLocator;
use LizardsAndPumpkins\Projection\Catalog\PageTitle\TwentyOneRunProductPageTitle;

class TwentyOneRunSimpleProductView extends AbstractProductView
{
    const MAX_PURCHASABLE_QTY = 5;

    /**
     * @var Product
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
        Product $product,
        TwentyOneRunProductPageTitle $pageTitle,
        ProductImageFileLocator $productImageFileLocator
    ) {
        $this->product = $product;
        $this->pageTitle = $pageTitle;
        $this->productImageFileLocator = $productImageFileLocator;
    }

    /**
     * {@inheritdoc}
     */
    final public function getOriginalProduct()
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
     * @param ProductAttribute $attribute
     * @return bool
     */
    final protected function isAttributePublic(ProductAttribute $attribute)
    {
        return (in_array($attribute->getCode(), ['backorders'])) ?
            false :
            parent::isAttributePublic($attribute);
    }

    /**
     * @param ProductAttribute $attribute
     * @return ProductAttribute
     */
    final protected function getProcessedAttribute(ProductAttribute $attribute)
    {
        if ($attribute->getCode() == 'stock_qty') {
            return $this->getBoundedStockQtyAttribute($attribute);
        }
        return parent::getProcessedAttribute($attribute);
    }

    /**
     * @return string
     */
    final public function getProductPageTitle()
    {
        return $this->pageTitle->forProductView($this);
    }

    /**
     * @param ProductAttribute $stockQty
     * @return ProductAttribute
     */
    private function getBoundedStockQtyAttribute(ProductAttribute $stockQty)
    {
        if ($this->isOverMaxQtyToShow($stockQty) || $this->hasBackorders()) {
            return $this->createStockQtyAttributeAtMaximumPurchasableLevel($stockQty);
        }

        return $stockQty;
    }

    /**
     * @param ProductAttribute $stockQty
     * @return bool
     */
    private function isOverMaxQtyToShow(ProductAttribute $stockQty)
    {
        return $stockQty->getValue() > self::MAX_PURCHASABLE_QTY;
    }

    /**
     * @return bool
     */
    private function hasBackorders()
    {
        return $this->product->getFirstValueOfAttribute('backorders') === 'true';
    }

    /**
     * @param ProductAttribute $attribute
     * @return ProductAttribute
     */
    private function createStockQtyAttributeAtMaximumPurchasableLevel(ProductAttribute $attribute)
    {
        return new ProductAttribute('stock_qty', self::MAX_PURCHASABLE_QTY, $attribute->getContextDataSet());
    }
}
