<?php

namespace Brera\Product\Block;

use Brera\Product\ProductSource;
use Brera\Product\ProductAttributeNotFoundException;
use Brera\Renderer\Block;

class ProductDetailsPageBlock extends Block
{
    /**
     * @param string $attributeCode
     * @return string
     * @throws ProductAttributeNotFoundException
     */
    public function getProductAttributeValue($attributeCode)
    {
        try {
            $product = $this->getProduct();
            $value = $product->getAttributeValue($attributeCode);
        } catch (ProductAttributeNotFoundException $e) {
            /* TODO: Log */
            $value = '';
        }
        return $value;
    }

    /**
     * @return \Brera\Product\ProductId
     */
    public function getProductId()
    {
        return $this->getProduct()->getId();
    }

    /**
     * @return ProductSource
     */
    private function getProduct()
    {
        return $this->getDataObject();
    }
}
