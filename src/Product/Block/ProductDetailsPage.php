<?php

namespace Brera\Product\Block;

use Brera\Product\ProductAttributeNotFoundException;
use Brera\Renderer\Block;

class ProductDetailsPage extends Block
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
     * @return \Brera\Product\Product
     */
    private function getProduct()
    {
        return $this->getDataObject();
    }
}
