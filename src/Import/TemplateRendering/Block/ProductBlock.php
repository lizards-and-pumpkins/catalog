<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\TemplateRendering\Block;

use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\Import\Product\View\ProductView;
use LizardsAndPumpkins\Import\TemplateRendering\Block;

class ProductBlock extends Block
{
    public function getFirstValueOfProductAttribute(string $attributeCode) : string
    {
        return $this->getProduct()->getFirstValueOfAttribute($attributeCode);
    }

    public function getImplodedValuesOfProductAttribute(string $attributeCode, string $glue) : string
    {
        $attributeValues = $this->getProduct()->getAllValuesOfAttribute($attributeCode);

        return implode($glue, $attributeValues);
    }

    public function getProductUrl() : string
    {
        return $this->getBaseUrl() . $this->getFirstValueOfProductAttribute(Product::URL_KEY);
    }

    public function getProductId() : ProductId
    {
        return $this->getProduct()->getId();
    }

    public function getMainProductImageLabel() : string
    {
        return $this->getProduct()->getMainImageLabel();
    }

    public function getMainProductImageUrl(string $variantCode) : HttpUrl
    {
        return $this->getProduct()->getMainImageUrl($variantCode);
    }

    private function getProduct() : ProductView
    {
        return $this->getDataObject();
    }

    public function getProductImageCount() : int
    {
        return $this->getProduct()->getImageCount();
    }

    public function getProductImageUrlByNumber(int $imageNumber, string $variantCode) : HttpUrl
    {
        return $this->getProduct()->getImageUrlByNumber($imageNumber, $variantCode);
    }

    public function getProductStockQuantity() : string
    {
        return $this->getProduct()->getFirstValueOfAttribute('stock_qty');
    }
}
