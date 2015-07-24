<?php

namespace Brera\Product\Block;

use Brera\Image;
use Brera\Product\Product;
use Brera\Product\ProductAttributeNotFoundException;
use Brera\Product\ProductId;
use Brera\Renderer\Block;

class ProductBlock extends Block
{
    /**
     * @param string $attributeCode
     * @return string
     * @throws ProductAttributeNotFoundException
     */
    public function getProductAttributeValue($attributeCode)
    {
        return $this->getProduct()->getAttributeValue($attributeCode);
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
     * @return ProductId
     */
    public function getProductId()
    {
        return $this->getProduct()->getId();
    }

    /**
     * @return string
     */
    public function getBrandLogoSrc()
    {
        $brandName = $this->getProductAttributeValue('brand');
        $brand = strtolower(preg_replace('/\W/', '_', trim($brandName)));
        $fileName = 'images/brands/brands-slider/' . $brand . '.png';

        if (!file_exists('pub/' . $fileName)) {
            return '';
        }

        return $fileName;
    }

    /**
     * @return Product
     */
    private function getProduct()
    {
        return $this->getDataObject();
    }
}
