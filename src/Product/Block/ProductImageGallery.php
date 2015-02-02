<?php

namespace Brera\Product\Block;

use Brera\Image;
use Brera\Renderer\Block;

class ProductImageGallery extends Block
{
    /**
     * @return Image
     */
    public function getMainProductImage()
    {
        $product = $this->getProduct();
        $images = $product->getAttributeValue('image');
        $imageFile = $images->getAttribute('file');
        $imageLabel = $images->getAttribute('label');

        return new Image($imageFile->getValue(), $imageLabel->getValue());
    }

    /**
     * @return \Brera\Product\Product
     */
    private function getProduct()
    {
        return $this->getDataObject();
    }
}
