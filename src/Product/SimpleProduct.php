<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Product\Exception\ProductTypeCodeMismatchException;
use LizardsAndPumpkins\Product\Exception\ProductTypeCodeMissingException;

class SimpleProduct implements Product
{
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
     * @param mixed[] $sourceArray
     * @return SimpleProduct
     */
    public static function fromArray(array $sourceArray)
    {
        self::validateSourceArray($sourceArray);
        return new self(
            ProductId::fromString($sourceArray['product_id']),
            ProductAttributeList::fromArray($sourceArray['attributes']),
            ProductImageList::fromArray($sourceArray['images']),
            ContextBuilder::rehydrateContext($sourceArray['context'])
        );
    }

    /**
     * @param mixed[] $sourceArray
     */
    private static function validateSourceArray(array $sourceArray)
    {
        if (! isset($sourceArray[Product::TYPE_KEY])) {
            $message = sprintf('The array key "%s" is missing from source array', Product::TYPE_KEY);
            throw new ProductTypeCodeMissingException($message);
        }
        if (self::TYPE_CODE !== $sourceArray[Product::TYPE_KEY]) {
            $variableType = self::getStringRepresentation($sourceArray[Product::TYPE_KEY]);
            $message = sprintf('Expected the product type code string "%s", got "%s"', self::TYPE_CODE, $variableType);
            throw new ProductTypeCodeMismatchException($message);
        }
    }

    /**
     * @param mixed $variable
     * @return string
     */
    private static function getStringRepresentation($variable)
    {
        if (is_string($variable)) {
            return $variable;
        }
        return is_object($variable) ?
            get_class($variable) :
            gettype($variable);
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
     * @return mixed[]
     */
    public function jsonSerialize()
    {
        return [
            'product_id' => (string) $this->productId,
            'type_code' => self::TYPE_CODE,
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
