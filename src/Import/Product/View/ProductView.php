<?php

namespace LizardsAndPumpkins\Import\Product\View;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Import\Product\ProductDTO;
use LizardsAndPumpkins\Import\Product\ProductAttributeList;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\Import\ImageStorage\Image;

interface ProductView extends \JsonSerializable
{
    /**
     * @return ProductDTO
     */
    public function getOriginalProduct();
    
    /**
     * @return ProductId
     */
    public function getId();

    /**
     * @param string $attributeCode
     * @return string
     */
    public function getFirstValueOfAttribute($attributeCode);

    /**
     * @param string $attributeCode
     * @return string[]
     */
    public function getAllValuesOfAttribute($attributeCode);

    /**
     * @param string $attributeCode
     * @return bool
     */
    public function hasAttribute($attributeCode);

    /**
     * @return ProductAttributeList
     */
    public function getAttributes();

    /**
     * @return Context
     */
    public function getContext();

    /**
     * @param string $variation
     * @return Image[]
     */
    public function getImages($variation);
    
    /**
     * @return int
     */
    public function getImageCount();

    /**
     * @param int $imageNumber
     * @param string $variation
     * @return Image
     */
    public function getImageByNumber($imageNumber, $variation);

    /**
     * @param int $imageNumber
     * @param string $variation
     * @return HttpUrl
     */
    public function getImageUrlByNumber($imageNumber, $variation);
    
    /**
     * @param int $imageNumber
     * @return string
     */
    public function getImageLabelByNumber($imageNumber);

    /**
     * @param string $variation
     * @return HttpUrl
     */
    public function getMainImageUrl($variation);

    /**
     * @return string
     */
    public function getMainImageLabel();

    /**
     * @return string
     */
    public function getProductPageTitle();
}
