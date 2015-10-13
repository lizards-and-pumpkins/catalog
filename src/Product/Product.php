<?php
namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;

interface Product extends \JsonSerializable
{
    const URL_KEY = 'url_key';
    const ID = 'product_id';
    const TYPE_KEY = 'type_code';
    
    /**
     * @return ProductId
     */
    public function getId();

    /**
     * @param Context $context
     * @return bool
     */
    public function isAvailableInContext(Context $context);

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
     * @return ProductImageList
     */
    public function getImages();

    /**
     * @return int
     */
    public function getImageCount();

    /**
     * @param int $imageNumber
     * @return ProductImage
     */
    public function getImageByNumber($imageNumber);

    /**
     * @param int $imageNumber
     * @return string
     */
    public function getImageFileNameByNumber($imageNumber);

    /**
     * @param int $imageNumber
     * @return string
     */
    public function getImageLabelByNumber($imageNumber);

    /**
     * @return string
     */
    public function getMainImageFileName();

    /**
     * @return string
     */
    public function getMainImageLabel();
}
