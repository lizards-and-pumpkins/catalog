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
    public function getFirstValueOfProductAttribute($attributeCode)
    {
        return $this->getProduct()->getFirstValueOfAttribute($attributeCode);
    }

    /**
     * @param string $attributeCode
     * @param string $glue
     * @return string
     */
    public function getImplodedValuesOfProductAttribute($attributeCode, $glue)
    {
        $attributeValues = $this->getProduct()->getAllValuesOfAttribute($attributeCode);

        return implode($glue, $attributeValues);
    }

    /**
     * @return string
     */
    public function getProductUrl()
    {
        return '/brera/' . $this->getFirstValueOfProductAttribute('url_key');
    }

    /**
     * @return Image
     */
    public function getMainProductImage()
    {
        $product = $this->getProduct();

        /**
         * todo: getAttributeValue should always return a string.
         * todo: For images, it would be better to have a dedicated method, for example getImage or getAsset
         */
        $image = $product->getFirstValueOfAttribute('image');
        $imageFile = $image->getAttributesWithCode('file')[0];
        $imageLabel = $image->getAttributesWithCode('label')[0];

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
        $brandName = $this->getFirstValueOfProductAttribute('brand');
        $brand = strtolower(preg_replace('/\W/', '_', trim($brandName)));
        $fileName = 'images/brands/brands-slider/' . $brand . '.png';

        if (!file_exists('pub/' . $fileName)) {
            return '';
        }

        return '/brera/' . $fileName;
    }

    /**
     * @return Product
     */
    private function getProduct()
    {
        return $this->getDataObject();
    }
}
