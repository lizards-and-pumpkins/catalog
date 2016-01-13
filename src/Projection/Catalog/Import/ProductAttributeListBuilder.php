<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\Projection\Catalog\Import\Exception\ProductAttributeContextPartsMismatchException;
use LizardsAndPumpkins\Product\ProductAttribute;
use LizardsAndPumpkins\Product\ProductAttributeList;

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
    public static function fromArray(array $attributesArray)
    {
        $attributes = array_reduce($attributesArray, function (array $carry, array $attributeArray) {
            if (trim($attributeArray[ProductAttribute::VALUE]) === '') {
                return $carry;
            }
            return array_merge($carry, [ProductAttribute::fromArray($attributeArray)]);
        }, []);

        return new self(...$attributes);
    }

    public function __construct(ProductAttribute ...$attributes)
    {
        self::validateAttributesMayBeCombinedIntoList(...$attributes);
        $this->attributes = $attributes;
    }

    private static function validateAttributesMayBeCombinedIntoList(ProductAttribute ...$attributes)
    {
        array_map(function (array $attributesByCode) {
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
            if (!$first->hasSameContextPartsAs($attributeToCompare)) {
                $message = self::getAttributeContextPartsMismatchExceptionMessage($first);
                throw new ProductAttributeContextPartsMismatchException($message);
            }
        }, $others);
    }

    /**
     * @param ProductAttribute $attribute
     * @return string
     */
    private static function getAttributeContextPartsMismatchExceptionMessage(ProductAttribute $attribute)
    {
        return sprintf('The attribute "%s" has multiple values with different contexts ' .
            'which can not be part of one product attribute list', $attribute->getCode());
    }

    /**
     * @param Context $context
     * @return ProductAttributeList
     */
    public function getAttributeListForContext(Context $context)
    {
        $extractedAttributes = $this->extractAttributesForContext($context);
        return new ProductAttributeList(...$extractedAttributes);
    }

    /**
     * @param Context $context
     * @return ProductAttribute[]
     */
    private function extractAttributesForContext(Context $context)
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
    private function getAttributeCode()
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
    private function getAttributesByCodeFromArray(array $attributes, $code)
    {
        return array_values(array_filter($attributes, function (ProductAttribute $attribute) use ($code) {
            return $attribute->isCodeEqualTo($code);
        }));
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
}
