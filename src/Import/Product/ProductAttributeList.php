<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Import\Product\Exception\ConflictingContextDataForProductAttributeListException;
use LizardsAndPumpkins\Import\Product\Exception\ProductAttributeNotFoundException;

class ProductAttributeList implements \Countable, \JsonSerializable
{
    /**
     * @var ProductAttribute[]
     */
    private $attributes;

    /**
     * @var AttributeCode[]
     */
    private $attributeCodes;

    /**
     * @param mixed[] $attributesArray
     * @return ProductAttributeList
     */
    public static function fromArray(array $attributesArray) : ProductAttributeList
    {
        $attributes = array_map(function (array $attributeArray) {
            return ProductAttribute::fromArray($attributeArray);
        }, $attributesArray);
        return new self(...$attributes);
    }

    public function __construct(ProductAttribute ...$attributes)
    {
        $this->attributes = $attributes;
        $this->validateAllAttributesHaveCompatibleContextData(...$attributes);
        $this->initializeAttributeCodesArray(...$attributes);
    }

    private function validateAllAttributesHaveCompatibleContextData(ProductAttribute ...$attributes)
    {
        array_reduce($attributes, function (array $attributeListContextParts, ProductAttribute $attribute) {
            every($attribute->getContextParts(), function ($contextPart) use ($attribute, $attributeListContextParts) {
                $this->validateContextPartIsValidInAttributeList($attribute, $contextPart, $attributeListContextParts);
            });

            return array_merge($attributeListContextParts, $attribute->getContextDataSet());
        }, []);
    }

    /**
     * @param ProductAttribute $attribute
     * @param string $part
     * @param string[] $attributeListContextParts
     */
    private function validateContextPartIsValidInAttributeList(
        ProductAttribute $attribute,
        string $part,
        array $attributeListContextParts
    ) {
        if (isset($attributeListContextParts[$part])) {
            $attributeContextPartValue = $attribute->getContextPartValue($part);
            $this->validateContextPartValuesMatch($part, $attributeListContextParts[$part], $attributeContextPartValue);
        }
    }

    private function validateContextPartValuesMatch(string $contextPart, string $valueA, string $valueB)
    {
        if ($valueA !== $valueB) {
            throw $this->getConflictingContextDataFoundException($contextPart, $valueA, $valueB);
        }
    }

    private function getConflictingContextDataFoundException(
        string $contextPart,
        string $valueA,
        string $valueB
    ) : ConflictingContextDataForProductAttributeListException {
        $message = sprintf('Conflicting context "%s" data set values found for attributes ' .
            'to be included in one attribute list: "%s" != "%s"', $contextPart, $valueA, $valueB);
        return new ConflictingContextDataForProductAttributeListException($message);
    }

    private function initializeAttributeCodesArray(ProductAttribute ...$attributes)
    {
        $this->attributeCodes = array_reduce($attributes, function (array $carry, ProductAttribute $attribute) {
            return array_merge($carry, [(string)$attribute->getCode() => $attribute->getCode()]);
        }, []);
    }

    /**
     * @param string|AttributeCode $code
     * @return ProductAttribute[]
     */
    public function getAttributesWithCode($code) : array
    {
        $attributeCode = AttributeCode::fromString($code);
        if (!$this->hasAttribute($attributeCode)) {
            throw new ProductAttributeNotFoundException(sprintf('Can not find an attribute with code "%s".', $code));
        }

        return $this->getAttributesByCodeFromArray($this->attributes, $code);
    }

    /**
     * @param ProductAttribute[] $attributes
     * @param string|AttributeCode $code
     * @return ProductAttribute[]
     */
    private function getAttributesByCodeFromArray(array $attributes, $code) : array
    {
        return array_values(array_filter($attributes, function (ProductAttribute $attribute) use ($code) {
            return $attribute->isCodeEqualTo($code);
        }));
    }

    /**
     * @param string|AttributeCode $attributeCode
     * @return bool
     */
    public function hasAttribute($attributeCode) : bool
    {
        return isset($this->attributeCodes[(string)$attributeCode]);
    }

    /**
     * @return AttributeCode[]
     */
    public function getAttributeCodes() : array
    {
        return array_values($this->attributeCodes);
    }

    public function count() : int
    {
        return count($this->attributes);
    }

    /**
     * @return array[]
     */
    public function jsonSerialize() : array
    {
        return array_map(function (ProductAttribute $productAttribute) {
            return $productAttribute->jsonSerialize();
        }, $this->attributes);
    }

    /**
     * @return ProductAttribute[]
     */
    public function getAllAttributes() : array
    {
        return $this->attributes;
    }
}
