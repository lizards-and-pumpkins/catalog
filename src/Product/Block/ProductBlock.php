<?php

namespace LizardsAndPumpkins\Product\Block;

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
        return $this->getBaseUrl() . $this->getFirstValueOfProductAttribute(Product::URL_KEY);
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

        return $this->getBaseUrl() . $fileName;
    }

    /**
     * @return string
     */
    public function getMainProductImageLabel()
    {
        return $this->getProduct()->getMainImageLabel();
    }

    /**
     * @return string
     */
    public function getMainProductFileName()
    {
        return $this->getProduct()->getMainImageFileName();
    }

    /**
     * @return Product
     */
    private function getProduct()
    {
        return $this->getDataObject();
    }

    /**
     * @return int
     */
    public function getProductImageCount()
    {
        return $this->getProduct()->getImageCount();
    }

    /**
     * @param int $imageNumber
     * @return string
     */
    public function getProductImageFileNameByNumber($imageNumber)
    {
        return $this->getProduct()->getImageFileNameByNumber($imageNumber);
    }

    /**
     * @return int
     */
    public function getProductStockQuantity()
    {
        return $this->getProduct()->getFirstValueOfAttribute('stock_qty');
    }
}
