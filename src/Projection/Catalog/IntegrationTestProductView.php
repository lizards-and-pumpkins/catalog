<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductAttributeList;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Product\ProductImage;
use LizardsAndPumpkins\Product\ProductImageList;
use LizardsAndPumpkins\Product\Tax\ProductTaxClass;

class IntegrationTestProductView implements ProductView
{
    /**
     * @var Product
     */
    private $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * @return Product
     */
    public function getOriginalProduct()
    {
        return $this->product;
    }

    /**
     * @return ProductId
     */
    public function getId()
    {
        return $this->product->getId();
    }

    /**
     * @param string $attributeCode
     * @return string
     */
    public function getFirstValueOfAttribute($attributeCode)
    {
        return $this->product->getFirstValueOfAttribute($attributeCode);
    }

    /**
     * @param string $attributeCode
     * @return string[]
     */
    public function getAllValuesOfAttribute($attributeCode)
    {
        return $this->product->getAllValuesOfAttribute($attributeCode);
    }

    /**
     * @param string $attributeCode
     * @return bool
     */
    public function hasAttribute($attributeCode)
    {
        return $this->product->hasAttribute($attributeCode);
    }

    /**
     * @return ProductAttributeList
     */
    public function getAttributes()
    {
        $this->product->getAttributes();
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->product->getContext();
    }

    /**
     * @return ProductImageList
     */
    public function getImages()
    {
        return $this->product->getImages();
    }

    /**
     * @return int
     */
    public function getImageCount()
    {
        return $this->product->getImageCount();
    }

    /**
     * @param int $imageNumber
     * @return ProductImage
     */
    public function getImageByNumber($imageNumber)
    {
        return $this->product->getImageByNumber($imageNumber);
    }

    /**
     * @param int $imageNumber
     * @return string
     */
    public function getImageFileNameByNumber($imageNumber)
    {
        return $this->product->getImageFileNameByNumber($imageNumber);
    }

    /**
     * @param int $imageNumber
     * @return string
     */
    public function getImageLabelByNumber($imageNumber)
    {
        return $this->product->getImageLabelByNumber($imageNumber);
    }

    /**
     * @return string
     */
    public function getMainImageFileName()
    {
        return $this->product->getMainImageFileName();
    }

    /**
     * @return string
     */
    public function getMainImageLabel()
    {
        return $this->product->getMainImageLabel();
    }

    /**
     * @return ProductTaxClass
     */
    public function getTaxClass()
    {
        return $this->product->getTaxClass();
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize()
    {
        return $this->product->jsonSerialize();
    }
}
