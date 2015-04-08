<?php

namespace Brera\Product\Block;

use Brera\Image;
use Brera\Product\ProductAttributeNotFoundException;
use Brera\Product\ProductSource;
use Brera\Renderer\Block;

abstract class ProductBlock extends Block
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
     * @return string
     */
    public function getProductUrl()
    {
        return $this->getProductAttributeValue('url_key');
    }

    /**
     * @return Image
     */
    public function getMainProductImage()
    {
        $product = $this->getProduct();

        /**
         * @todo: getAttributeValue should always return a string.
         * @todo: For images, it would be better to have a dedicated method,
         * @todo: for example getImage or getAsset
         */
        $image = $product->getAttributeValue('image');
        $imageFile = $image->getAttribute('file');
        $imageLabel = $image->getAttribute('label');

        return new Image($imageFile->getValue(), $imageLabel->getValue());
    }

    /**
     * @return ProductSource
     */
    final protected function getProduct()
    {
        return $this->getDataObject();
    }
}
