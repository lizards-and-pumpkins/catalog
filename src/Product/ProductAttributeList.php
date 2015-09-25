<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\Exception\ProductAttributeContextPartsMismatchException;
use LizardsAndPumpkins\Product\Exception\ProductAttributeNotFoundException;

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
    public static function fromArray(array $attributesArray)
    {
        $attributes = array_map(function (array $attributeArray) {
            return ProductAttribute::fromArray($attributeArray);
        }, $attributesArray);
        return new self(...$attributes);
    }

    public function __construct(ProductAttribute ...$attributes)
    {
        self::validateAttributesMayBeCombinedIntoList(...$attributes);
        $this->attributes = $attributes;
        $this->initializeAttributeCodesArray(...$attributes);
    }

    private static function validateAttributesMayBeCombinedIntoList(ProductAttribute ...$attributes)
    {
        array_map(function ($attributesByCode) {
            self::validateAttributesHaveSameContextParts(...$attributesByCode);
        }, self::getAttributesGroupedByCode($attributes));
    }

    /**
     * @param ProductAttribute[] $attributes
     * @return array[]
     */
    private static function getAttributesGroupedByCode(array $attributes)
    {
        return array_reduce($attributes, function ($carry, ProductAttribute $attribute) {
            $carry[(string)$attribute->getCode()][] = $attribute;
            return $carry;
        }, []);
    }

    private static function validateAttributesHaveSameContextParts(ProductAttribute $first, ProductAttribute ...$others)
    {
        array_map(function (ProductAttribute $attributeToCompare) use ($first) {
            if (! $first->hasSameContextPartsAs($attributeToCompare)) {
                self::throwAttributeContextPartsMismatchException($first);
                // @codeCoverageIgnoreStart
            }
            // @codeCoverageIgnoreEnd
        }, $others);
    }

    private static function throwAttributeContextPartsMismatchException(ProductAttribute $attributeA)
    {
        $message = sprintf(
            'The attribute "%s" has multiple values with different contexts ' .
            'which can not be part of one product attribute list',
            $attributeA->getCode()
        );
        throw new ProductAttributeContextPartsMismatchException($message);
    }

    private function initializeAttributeCodesArray(ProductAttribute ...$attributes)
    {
        $this->attributeCodes = array_reduce($attributes, function (array $carry, ProductAttribute $attribute) {
            return array_merge($carry, [(string)$attribute->getCode() => $attribute->getCode()]);
        }, []);
    }

    /**
     * @param string $code
     * @return ProductAttribute[]
     */
    public function getAttributesWithCode($code)
    {
        $attributeCode = AttributeCode::fromString($code);
        if (!$this->hasAttribute($attributeCode)) {
            throw new ProductAttributeNotFoundException(sprintf('Can not find an attribute with code "%s".', $code));
        }

        return self::getAttributesByCodeFromArray($this->attributes, $code);
    }

    /**
     * @param ProductAttribute[] $attributes
     * @param string|AttributeCode $code
     * @return ProductAttribute[]
     */
    private static function getAttributesByCodeFromArray(array $attributes, $code)
    {
        return array_values(array_filter($attributes, function (ProductAttribute $attribute) use ($code) {
            return $attribute->isCodeEqualTo($code);
        }));
    }

    /**
     * @param Context $context
     * @return ProductAttributeList
     */
    public function getAttributeListForContext(Context $context)
    {
        $extractedAttributes = $this->extractAttributesForContext($context);
        return new self(...$extractedAttributes);
    }

    /**
     * @param Context $context
     * @return ProductAttribute[]
     */
    private function extractAttributesForContext(Context $context)
    {
        return array_reduce($this->attributeCodes, function (array $carry, AttributeCode $code) use ($context) {
            $attributesForCode = $this->getAttributesWithCode($code);
            return array_merge($carry, $this->getAttributesMatchingContext($attributesForCode, $context));
        }, []);
    }

    /**
     * @param ProductAttribute[] $productAttributes
     * @param Context $context
     * @return ProductAttribute
     */
    private function getAttributesMatchingContext(array $productAttributes, Context $context)
    {
        return array_filter($productAttributes, function (ProductAttribute $attribute) use ($context) {
            return $context->matchesDataSet($attribute->getContextDataSet());
        });
    }

    /**
     * @param string|AttributeCode $attributeCode
     * @return bool
     */
    public function hasAttribute($attributeCode)
    {
        return isset($this->attributeCodes[(string)$attributeCode]);
    }

    /**
     * @return AttributeCode[]
     */
    public function getAttributeCodes()
    {
        return array_values($this->attributeCodes);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->attributes);
    }

    /**
     * @return string[]
     */
    public function jsonSerialize()
    {
        return array_reduce($this->attributes, function ($carry, ProductAttribute $attribute) {
            $carry[] = $attribute->jsonSerialize();
            return $carry;
        }, []);
    }
}
