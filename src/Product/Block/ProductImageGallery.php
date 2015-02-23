<?php

namespace Brera\Product\Block;

use Brera\Image;
use Brera\Product\ProductSource;
use Brera\Renderer\Block;

class ProductImageGallery extends Block
{
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
    private function getProduct()
    {
        return $this->getDataObject();
    }
}
