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
    private $attributes = [];

    /**
     * @var string[]
     */
    private $attributeCodes = [];

    /**
     * @param mixed[] $attributesArray
     * @return ProductAttributeList
     */
    public static function fromArray(array $attributesArray)
    {
        $attributeList = new self();

        foreach ($attributesArray as $attributeArray) {
            $attribute = ProductAttribute::fromArray($attributeArray);
            $attributeList->add($attribute);
        }

        return $attributeList;
    }

    public function add(ProductAttribute $attribute)
    {
        $this->validateAttributeMayBeAddedToList($attribute);

        $this->attributes[] = $attribute;
        $this->addAttributeCode($attribute->getCode());
    }
    
    private function validateAttributeMayBeAddedToList(ProductAttribute $attribute)
    {
        foreach ($this->getAttributesByCodeWithoutValidation($attribute->getCode()) as $attributeInList) {
            if (!$attribute->hasSameContextPartsAs($attributeInList)) {
                throw new ProductAttributeContextPartsMismatchException(
                    'Attributes with different context parts can not be combined into one list'
                );
            }
        }
    }

    /**
     * @param string $attributeCode
     */
    private function addAttributeCode($attributeCode)
    {
        if (!$this->hasAttribute($attributeCode)) {
            $this->attributeCodes[$attributeCode] = $attributeCode;
        }
    }

    /**
     * @param string $code
     * @return ProductAttribute[]
     */
    public function getAttributesWithCode($code)
    {
        if (empty($code)) {
            throw new ProductAttributeNotFoundException('Can not get an attribute with blank code.');
        }
        if (!$this->hasAttribute($code)) {
            throw new ProductAttributeNotFoundException(sprintf('Can not find an attribute with code "%s".', $code));
        }

        return $this->getAttributesByCodeWithoutValidation($code);
    }

    /**
     * @param string $code
     * @return ProductAttribute[]
     */
    private function getAttributesByCodeWithoutValidation($code)
    {
        return array_values(array_filter($this->attributes, function (ProductAttribute $attribute) use ($code) {
            return $attribute->isCodeEqualsTo($code);
        }));
    }

    /**
     * @param Context $context
     * @return ProductAttributeList
     */
    public function getAttributeListForContext(Context $context)
    {
        $extractedAttributes = $this->extractAttributesForContext($context);
        return $this->createAttributeListFromAttributeArray($extractedAttributes);
    }

    /**
     * @param Context $context
     * @return ProductAttribute[]
     */
    private function extractAttributesForContext(Context $context)
    {
        return array_reduce($this->attributeCodes, function (array $carry, $code) use ($context) {
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
     * @param ProductAttribute[] $productAttributes
     * @return ProductAttributeList
     */
    private function createAttributeListFromAttributeArray(array $productAttributes)
    {
        $attributeList = new self();
        array_walk($productAttributes, function (ProductAttribute $attribute) use ($attributeList) {
            $attributeList->add($attribute);
        });
        return $attributeList;
    }

    /**
     * @param string $attributeCode
     * @return bool
     */
    public function hasAttribute($attributeCode)
    {
        return isset($this->attributeCodes[$attributeCode]);
    }

    /**
     * @return string[]
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
        return array_reduce($this->attributes, function($carry, ProductAttribute $attribute) {
            if (!isset($carry[$attribute->getCode()])) {
                $carry[$attribute->getCode()] = [];
            }
            $carry[$attribute->getCode()][] = $attribute->getValue();
            return $carry;
        }, []);
    }
}
