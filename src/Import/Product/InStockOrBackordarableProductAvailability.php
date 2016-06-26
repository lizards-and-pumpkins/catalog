<?php

namespace LizardsAndPumpkins\Import\Product;

class InStockOrBackordarableProductAvailability implements ProductAvailability
{
    /**
     * @param Product $product
     * @return bool
     */
    public function isProductSalable(Product $product)
    {
        return $this->isAvailableForBackorders($product) || $this->hasPositiveStockQty($product);
    }

    /**
     * @param Product $product
     * @return bool
     */
    private function isAvailableForBackorders(Product $product)
    {
        return $product->getFirstValueOfAttribute('backorders') === 'true';
    }

    /**
     * @param Product $product
     * @return bool
     */
    private function hasPositiveStockQty(Product $product)
    {
        return (int) $product->getFirstValueOfAttribute('stock_qty') > 0;
    }
}
