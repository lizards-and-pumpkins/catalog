<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\SelfContainedContextBuilder;
use LizardsAndPumpkins\Import\Product\Image\ProductImage;
use LizardsAndPumpkins\Import\Product\Image\ProductImageList;
use LizardsAndPumpkins\Import\Tax\ProductTaxClass;

class SimpleProduct implements Product
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

    public function __construct(
        ProductId $productId,
        ProductTaxClass $taxClass,
        ProductAttributeList $attributeList,
        ProductImageList $images,
        Context $context
    ) {
        $this->productId = $productId;
        $this->taxClass = $taxClass;
        $this->attributeList = $attributeList;
        $this->context = $context;
        $this->images = $images;
    }

    /**
     * @param mixed[] $sourceArray
     * @return SimpleProduct
     */
    public static function fromArray(array $sourceArray)
    {
        self::validateTypeCodeInSourceArray(self::TYPE_CODE, $sourceArray);
        return new self(
            new ProductId($sourceArray['product_id']),
            ProductTaxClass::fromString($sourceArray['tax_class']),
            ProductAttributeList::fromArray($sourceArray['attributes']),
            ProductImageList::fromImages(...$sourceArray['images']),
            SelfContainedContextBuilder::rehydrateContext($sourceArray[self::CONTEXT])
        );
    }

    public function getId() : ProductId
    {
        return $this->productId;
    }

    /**
     * @param string $attributeCode
     * @return mixed
     */
    public function getFirstValueOfAttribute(string $attributeCode)
    {
        $attributeValues = $this->getAllValuesOfAttribute($attributeCode);
        return $attributeValues[0] ?? '';
    }

    /**
     * @param string $attributeCode
     * @return string[]
     */
    public function getAllValuesOfAttribute(string $attributeCode) : array
    {
        if (!$this->attributeList->hasAttribute($attributeCode)) {
            return [];
        }
        return array_map(function (ProductAttribute $productAttribute) {
            return $productAttribute->getValue();
        }, $this->attributeList->getAttributesWithCode($attributeCode));
    }

    public function hasAttribute(AttributeCode $attributeCode) : bool
    {
        return $this->attributeList->hasAttribute($attributeCode);
    }

    public function getAttributes() : ProductAttributeList
    {
        return $this->attributeList;
    }
    
    /**
     * @return mixed[]
     */
    public function jsonSerialize() : array
    {
        return [
            'product_id' => (string) $this->productId,
            'tax_class' => (string) $this->taxClass,
            Product::TYPE_KEY => self::TYPE_CODE,
            'attributes' => $this->attributeList->jsonSerialize(),
            'images' => $this->images,
            self::CONTEXT => $this->context
        ];
    }

    public function getContext() : Context
    {
        return $this->context;
    }

    public function getImages() : ProductImageList
    {
        return $this->images;
    }

    public function getImageCount() : int
    {
        return count($this->images);
    }

    public function getImageByNumber(int $imageNumber) : ProductImage
    {
        return $this->images[$imageNumber];
    }

    public function getImageFileNameByNumber(int $imageNumber) : string
    {
        return $this->images[$imageNumber]->getFileName();
    }

    public function getImageLabelByNumber(int $imageNumber) : string
    {
        return $this->images[$imageNumber]->getLabel();
    }

    public function getMainImageFileName() : string
    {
        return $this->getImageFileNameByNumber(0);
    }

    public function getMainImageLabel() : string
    {
        return $this->getImageLabelByNumber(0);
    }

    public function getTaxClass() : ProductTaxClass
    {
        return $this->taxClass;
    }
}
