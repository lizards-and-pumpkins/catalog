<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Product\Exception\ProductAttributeContextPartsMismatchException;

class ProductAttributeListBuilder
{
    /**
     * @var ProductAttribute[]
     */
    private $attributes;

    /**
     * @param array[] $attributesArray
     * @return ProductAttributeListBuilder
     */
    public static function fromArray(array $attributesArray) : ProductAttributeListBuilder
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
    }

    private static function validateAttributesMayBeCombinedIntoList(ProductAttribute ...$attributes)
    {
        every(self::getAttributesGroupedByCode($attributes), function (array $attributesByCode) {
            self::validateAttributesHaveSameContextParts(...$attributesByCode);
        });
    }

    /**
     * @param ProductAttribute[] $attributes
     * @return ProductAttribute[]
     */
    private static function getAttributesGroupedByCode(array $attributes) : array
    {
        return array_reduce($attributes, function ($carry, ProductAttribute $attribute) {
            $carry[(string)$attribute->getCode()][] = $attribute;
            return $carry;
        }, []);
    }

    private static function validateAttributesHaveSameContextParts(ProductAttribute $first, ProductAttribute ...$others)
    {
        every($others, function (ProductAttribute $attributeToCompare) use ($first) {
            if (!$first->hasSameContextPartsAs($attributeToCompare)) {
                $message = self::getAttributeContextPartsMismatchExceptionMessage($first);
                throw new ProductAttributeContextPartsMismatchException($message);
            }
        });
    }

    private static function getAttributeContextPartsMismatchExceptionMessage(ProductAttribute $attribute) : string
    {
        return sprintf('The attribute "%s" has multiple values with different contexts ' .
            'which can not be part of one product attribute list', $attribute->getCode());
    }

    public function getAttributeListForContext(Context $context) : ProductAttributeList
    {
        $extractedAttributes = $this->extractAttributesForContext($context);
        return new ProductAttributeList(...$extractedAttributes);
    }

    /**
     * @param Context $context
     * @return ProductAttribute[]
     */
    private function extractAttributesForContext(Context $context) : array
    {
        $attributeCodes = $this->getAttributeCode();
        return array_reduce($attributeCodes, function (array $carry, AttributeCode $code) use ($context) {
            $attributesForCode = $this->getAttributesByCodeFromArray($this->attributes, $code);
            return array_merge($carry, $this->getAttributesMatchingContext($attributesForCode, $context));
        }, []);
    }

    /**
     * @return string[]
     */
    private function getAttributeCode() : array
    {
        return array_unique(array_map(function (ProductAttribute $attribute) {
            return $attribute->getCode();
        }, $this->attributes));
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
     * @param ProductAttribute[] $productAttributes
     * @param Context $context
     * @return ProductAttribute[]
     */
    private function getAttributesMatchingContext(array $productAttributes, Context $context) : array
    {
        return array_filter($productAttributes, function (ProductAttribute $attribute) use ($context) {
            return $context->matchesDataSet($attribute->getContextDataSet());
        });
    }
}
