<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;

class SimpleProduct implements \JsonSerializable
{
    const TYPE_ID = 'simple';
    const URL_KEY = 'url_key';
    const ID = 'product_id';

    /**
     * @var ProductId
     */
    private $productId;

    /**
     * @var ProductAttributeList
     */
    private $attributeList;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var ProductImageList
     */
    private $images;

    public function __construct(
        ProductId $productId,
        ProductAttributeList $attributeList,
        ProductImageList $images,
        Context $context
    ) {
        $this->productId = $productId;
        $this->attributeList = $attributeList;
        $this->context = $context;
        $this->images = $images;
    }

    /**
     * @return ProductId
     */
    public function getId()
    {
        return $this->productId;
    }

    /**
     * @param string $attributeCode
     * @return string
     */
    public function getFirstValueOfAttribute($attributeCode)
    {
        $attributeValues = $this->getAllValuesOfAttribute($attributeCode);

        return isset($attributeValues[0]) ?
            $attributeValues[0] :
            '';
    }

    /**
     * @param string $attributeCode
     * @return string[]
     */
    public function getAllValuesOfAttribute($attributeCode)
    {
        if (!$this->attributeList->hasAttribute($attributeCode)) {
            return [];
        }
        return array_map(function (ProductAttribute $productAttribute) {
            return $productAttribute->getValue();
        }, $this->attributeList->getAttributesWithCode($attributeCode));
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize()
    {
        return [
            'product_id' => (string)$this->productId,
            'attributes' => $this->attributeList,
            'images' => $this->images,
            'context' => $this->context
        ];
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return ProductImageList
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @return int
     */
    public function getImageCount()
    {
        return count($this->images);
    }

    /**
     * @param int $imageNumber
     * @return ProductImage
     */
    public function getImageByNumber($imageNumber)
    {
        return $this->images[$imageNumber];
    }

    /**
     * @param int $imageNumber
     * @return string
     */
    public function getImageFileNameByNumber($imageNumber)
    {
        return $this->images[$imageNumber]->getFileName();
    }

    /**
     * @param int $imageNumber
     * @return string
     */
    public function getImageLabelByNumber($imageNumber)
    {
        return $this->images[$imageNumber]->getLabel();
    }

    /**
     * @return string
     */
    public function getMainImageFileName()
    {
        return $this->getImageFileNameByNumber(0);
    }

    /**
     * @return string
     */
    public function getMainImageLabel()
    {
        return $this->getImageLabelByNumber(0);
    }
}
