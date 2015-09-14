<?php

namespace LizardsAndPumpkins\Product\Block;

use LizardsAndPumpkins\Image;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Renderer\Block;

class ProductBlock extends Block
{
    /**
     * @param string $attributeCode
     * @return string
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
        /* TODO: Implement retrieval of base URL for context */
        return '/lizards-and-pumpkins/' . $this->getFirstValueOfProductAttribute('url_key');
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

        return '/lizards-and-pumpkins/' . $fileName;
    }

    /**
     * @return Product
     */
    private function getProduct()
    {
        return $this->getDataObject();
    }
}
