<?php

namespace LizardsAndPumpkins\Product\Block;

use LizardsAndPumpkins\Image;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\SimpleProduct;
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
        return '/lizards-and-pumpkins/' . $this->getFirstValueOfProductAttribute(Product::URL_KEY);
    }

    /**
     * @return Image
     */
    public function getMainProductImage()
    {
        $product = $this->getProduct();
        
        return new Image($product->getMainImageFileName(), $product->getMainImageLabel());
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
