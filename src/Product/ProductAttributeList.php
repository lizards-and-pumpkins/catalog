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

    public function add(ProductAttribute $attribute)
    {
        foreach ($this->attributes as $attributeInList) {
            if ($attribute->hasSameCodeAs($attributeInList) && !$attribute->hasSameContextPartsAs($attributeInList)) {
                throw new AttributeContextPartsMismatchException(
                    'Attributes with different context parts can not be combined into a list'
                );
            }
        }

        $this->attributes[] = $attribute;
        $this->addAttributeCode($attribute->getCode());
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

        $attributesWithCode = [];

        foreach ($this->attributes as $attribute) {
            if ($attribute->isCodeEqualsTo($code)) {
                $attributesWithCode[] = $attribute;
            }
        }

        if (empty($attributesWithCode)) {
            throw new ProductAttributeNotFoundException(sprintf('Can not find an attribute with code "%s".', $code));
        }

        return $attributesWithCode;
    }

    /**
     * @param mixed[] $nodes
     * @return ProductAttributeList
     */
    public static function fromArray(array $nodes)
    {
        $attributeList = new self();

        foreach ($nodes as $node) {
            $attribute = ProductAttribute::fromArray($node);
            $attributeList->add($attribute);
        }

        return $attributeList;
    }

    /**
     * @param string $attributeCode
     */
    private function addAttributeCode($attributeCode)
    {
        if (!in_array($attributeCode, $this->attributeCodes)) {
            $this->attributeCodes[] = $attributeCode;
        }
    }

    /**
     * @param Context $context
     * @return ProductAttributeList
     */
    public function getAttributesForContext(Context $context)
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
        $attributesForContext = [];
        foreach ($this->attributeCodes as $code) {
            $attributesForCode = $this->getAttributesForCode($code);
            $attributesForContext[] = $this->getBestMatchingAttributeForContext(
                $attributesForCode,
                $context
            );
        }
        return $attributesForContext;
    }

    /**
     * @param string $code
     * @return ProductAttribute[]
     */
    private function getAttributesForCode($code)
    {
        $attributesForCode = [];
        foreach ($this->attributes as $attribute) {
            if ($attribute->getCode() === $code) {
                $attributesForCode[] = $attribute;
            }
        }
        return $attributesForCode;
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
}
