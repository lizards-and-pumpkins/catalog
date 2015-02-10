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
        $product = $this->getProduct();

        try {
            return $product->getAttributeValue($attributeCode);
        } catch (ProductAttributeNotFoundException $e) {
            /* TODO: Log */
            return '';
        }
    }

    /**
     * @return \Brera\Product\ProductId
     */
    public function getProductId()
    {
        $product = $this->getProduct();

        return $product->getId();
    }

    /**
     * @return ProductSource
     */
    private function getProduct()
    {
        return $this->getDataObject();
    }
}
