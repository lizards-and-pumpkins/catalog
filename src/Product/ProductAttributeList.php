<?php

namespace Brera\Product;

use Brera\Environment\Environment;

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
     * @param ProductAttribute $attribute
     * @return void
     */
    public function add(ProductAttribute $attribute)
    {
        array_push($this->attributes, $attribute);
        $this->addAttributeCode($attribute->getCode());
    }

    /**
     * @param string $code
     * @throws ProductAttributeNotFoundException
     * @return ProductAttribute
     */
    public function getAttribute($code)
    {
        if (empty($code)) {
            throw new ProductAttributeNotFoundException('Can not get an attribute with blank code.');
        }

        foreach ($this->attributes as $attribute) {
            if ($attribute->isCodeEqualsTo($code)) {
                return $attribute;
            }
        }

        throw new ProductAttributeNotFoundException(sprintf('Can not find an attribute with code "%s".', $code));
    }

    /**
     * @param array $nodes
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
     * @param Environment $environment
     * @return ProductAttributeList
     */
    public function getAttributesForEnvironment(Environment $environment)
    {
        $extractedAttributes = $this->extractAttributesForEnvironment($environment);
        return $this->createAttributeListFromAttributeArray($extractedAttributes);
    }

    /**
     * @param Environment $environment
     * @return ProductAttribute[]
     */
    private function extractAttributesForEnvironment(Environment $environment)
    {
        $attributesForEnvironment = [];
        foreach ($this->attributeCodes as $code) {
            $attributesForCode = $this->getAttributesForCode($code);
            $attributesForEnvironment[] = $this->getBestMatchingAttributeForEnvironment(
                $attributesForCode,
                $environment
            );
        }
        return $attributesForEnvironment;
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
     * @param Environment $environment
     * @return ProductAttribute
     */
    private function getBestMatchingAttributeForEnvironment(array $productAttributes, Environment $environment)
    {
        /** @var ProductAttribute $carry */
        return array_reduce($productAttributes, function ($carry, ProductAttribute $attribute) use ($environment) {
            return is_null($carry) ?
                $attribute :
                $this->returnMostMatchingAttributeForEnvironment($environment, $carry, $attribute);
        }, null);
    }

    /**
     * @param Environment $environment
     * @param ProductAttribute $attributeA
     * @param ProductAttribute $attributeB
     * @return ProductAttribute
     */
    private function returnMostMatchingAttributeForEnvironment(
        Environment $environment,
        ProductAttribute $attributeA,
        ProductAttribute $attributeB
    ) {
        $scoreB = $attributeB->getMatchScoreForEnvironment($environment);
        $scoreA = $attributeA->getMatchScoreForEnvironment($environment);
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
