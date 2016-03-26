<?php

namespace LizardsAndPumpkins\Import\TemplateRendering\Block;

use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\Import\Product\View\ProductView;
use LizardsAndPumpkins\Import\ContentBlock\Block;

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
    public function getMainProductImageLabel()
    {
        return $this->getProduct()->getMainImageLabel();
    }

    /**
     * @param string $variantCode
     * @return string
     */
    public function getMainProductImageUrl($variantCode)
    {
        return $this->getProduct()->getMainImageUrl($variantCode);
    }

    /**
     * @return ProductView
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
     * @param string $variantCode
     * @return string
     */
    public function getProductImageUrlByNumber($imageNumber, $variantCode)
    {
        return $this->getProduct()->getImageUrlByNumber($imageNumber, $variantCode);
    }

    /**
     * @return int
     */
    public function getProductStockQuantity()
    {
        return $this->getProduct()->getFirstValueOfAttribute('stock_qty');
    }
}
