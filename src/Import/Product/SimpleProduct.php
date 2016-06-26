<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\SelfContainedContextBuilder;
use LizardsAndPumpkins\Import\Product\Image\ProductImage;
use LizardsAndPumpkins\Import\Product\Image\ProductImageList;
use LizardsAndPumpkins\Import\Tax\ProductTaxClass;

class SimpleProduct implements ProductDomainModel
{
    use RehydrateableProductTrait;
    
    const CONTEXT = 'context';
    const TYPE_CODE = 'simple';

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
    
    /**
     * @var ProductTaxClass
     */
    private $taxClass;

    /**
     * @var DetermineProductAvailability
     */
    private $determineProductAvailability;

    public function __construct(
        ProductId $productId,
        ProductTaxClass $taxClass,
        ProductAttributeList $attributeList,
        ProductImageList $images,
        Context $context,
        DetermineProductAvailability $determineProductAvailability
    ) {
        $this->productId = $productId;
        $this->taxClass = $taxClass;
        $this->attributeList = $attributeList;
        $this->context = $context;
        $this->images = $images;
        $this->determineProductAvailability = $determineProductAvailability;
    }

    /**
     * @param mixed[] $sourceArray
     * @return SimpleProduct
     */
    public static function fromArray(array $sourceArray)
    {
        self::validateTypeCodeInSourceArray(self::TYPE_CODE, $sourceArray);
        return new self(
            ProductId::fromString($sourceArray['product_id']),
            ProductTaxClass::fromString($sourceArray['tax_class']),
            ProductAttributeList::fromArray($sourceArray['attributes']),
            ProductImageList::fromArray($sourceArray['images']),
            SelfContainedContextBuilder::rehydrateContext($sourceArray[self::CONTEXT])
        );
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
     * @param string $attributeCode
     * @return bool
     */
    public function hasAttribute($attributeCode)
    {
        return $this->attributeList->hasAttribute($attributeCode);
    }

    /**
     * @return ProductAttributeList
     */
    public function getAttributes()
    {
        return $this->attributeList;
    }
    
    /**
     * @return mixed[]
     */
    public function jsonSerialize()
    {
        return [
            'product_id'         => (string) $this->productId,
            'tax_class'          => (string) $this->taxClass,
            ProductDTO::TYPE_KEY => self::TYPE_CODE,
            'attributes'         => $this->attributeList->jsonSerialize(),
            'images'             => $this->images,
            self::CONTEXT        => $this->context
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

    /**
     * @return ProductTaxClass
     */
    public function getTaxClass()
    {
        return $this->taxClass;
    }

    /**
     * @return bool
     */
    public function isAvailable()
    {
        return $this->determineProductAvailability->forProduct($this);
    }
}
