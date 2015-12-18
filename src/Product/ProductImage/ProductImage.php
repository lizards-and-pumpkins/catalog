<?php


namespace LizardsAndPumpkins\Product\ProductImage;

use LizardsAndPumpkins\Product\ProductAttributeList;

class ProductImage implements \JsonSerializable
{
    const FILE = 'file';
    const LABEL = 'label';
    
    /**
     * @var ProductAttributeList
     */
    private $attributeList;

    public function __construct(ProductAttributeList $attributeList)
    {
        $this->attributeList = $attributeList;
    }

    /**
     * @param array[] $imageAttributeArray
     * @return ProductImage
     */
    public static function fromArray(array $imageAttributeArray)
    {
        return new self(ProductAttributeList::fromArray($imageAttributeArray));
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->attributeList->getAttributesWithCode(self::FILE)[0]->getValue();
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        if (! $this->attributeList->hasAttribute(self::LABEL)) {
            return '';
        }
        return $this->attributeList->getAttributesWithCode(self::LABEL)[0]->getValue();
    }

    /**
     * @return ProductAttributeList
     */
    public function jsonSerialize()
    {
        return $this->attributeList;
    }

    /**
     * @return ProductAttributeList
     */
    public function getAttributes()
    {
        return $this->attributeList;
    }
}
