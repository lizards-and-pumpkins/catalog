<?php

namespace Brera\Product;

use Brera\Context\Context;

class ProductAttributeList
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
        return array_map(function ($code) use ($context) {
            $attributesForCode = $this->getAttributesWithCode($code);
            return $this->getBestMatchingAttributeForContext($attributesForCode, $context);
        }, $this->attributeCodes);
    }

    /**
     * @param ProductAttribute[] $productAttributes
     * @param Context $context
     * @return ProductAttribute
     */
    private function getBestMatchingAttributeForContext(array $productAttributes, Context $context)
    {
        /** @var ProductAttribute $carry */
        return array_reduce($productAttributes, function ($carry, ProductAttribute $attribute) use ($context) {
            return is_null($carry) ?
                $attribute :
                $this->returnMostMatchingAttributeForContext($context, $carry, $attribute);
        }, null);
    }

    /**
     * @param Context $context
     * @param ProductAttribute $attributeA
     * @param ProductAttribute $attributeB
     * @return ProductAttribute
     */
    private function returnMostMatchingAttributeForContext(
        Context $context,
        ProductAttribute $attributeA,
        ProductAttribute $attributeB
    ) {
        $scoreB = $attributeB->getMatchScoreForContext($context);
        $scoreA = $attributeA->getMatchScoreForContext($context);
        return $scoreB > $scoreA ?
            $attributeB :
            $attributeA;
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
    private function hasAttribute($attributeCode)
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
}
