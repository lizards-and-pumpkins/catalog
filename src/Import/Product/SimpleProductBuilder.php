<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Price\Price;
use LizardsAndPumpkins\Import\Product\Image\ProductImageListBuilder;
use LizardsAndPumpkins\Import\Tax\ProductTaxClass;

class SimpleProductBuilder implements ProductBuilder
{
    /**
     * @var ProductId
     */
    private $id;

    /**
     * @var ProductAttributeListBuilder
     */
    private $attributeListBuilder;

    /**
     * @var ProductImageListBuilder
     */
    private $imageListBuilder;
    
    /**
     * @var ProductTaxClass
     */
    private $taxClass;

    public function __construct(
        ProductId $id,
        ProductTaxClass $taxClass,
        ProductAttributeListBuilder $attributeListBuilder,
        ProductImageListBuilder $imageListBuilder
    ) {
        $this->id = $id;
        $this->attributeListBuilder = $attributeListBuilder;
        $this->imageListBuilder = $imageListBuilder;
        $this->taxClass = $taxClass;
    }

    public function isAvailableForContext(Context $context) : bool
    {
        $sourceAttributeList = $this->attributeListBuilder->getAttributeListForContext($context);
        return count($sourceAttributeList) > 0;
    }

    public function getProductForContext(Context $context) : Product
    {
        $sourceAttributeList = $this->attributeListBuilder->getAttributeListForContext($context);
        $validSourceAttributes = $this->filterAttributesWithInvalidValues($sourceAttributeList);
        $attributesWithProperTypes = $this->ensureAttributeTypes($validSourceAttributes);
        $images = $this->imageListBuilder->getImageListForContext($context);
        return new SimpleProduct($this->id, $this->taxClass, $attributesWithProperTypes, $images, $context);
    }

    private function ensureAttributeTypes(ProductAttributeList $sourceAttributeList) : ProductAttributeList
    {
        $attributes = array_map([$this, 'ensureAttributeType'], $sourceAttributeList->getAllAttributes());
        return new ProductAttributeList(...$attributes);
    }

    private function ensureAttributeType(ProductAttribute $attribute) : ProductAttribute
    {
        if ($attribute->isCodeEqualTo('price') || $attribute->isCodeEqualTo('special_price')) {
            return $this->ensurePriceAttributeTypeInt($attribute);
        }
        return $attribute;
    }

    private function ensurePriceAttributeTypeInt(ProductAttribute $attribute) : ProductAttribute
    {
        if (is_int($attribute->getValue())) {
            return $attribute;
        }
        $price = Price::fromDecimalValue($attribute->getValue());
        return new ProductAttribute($attribute->getCode(), $price->getAmount(), $attribute->getContextDataSet());
    }

    private function filterAttributesWithInvalidValues(ProductAttributeList $sourceAttributeList): ProductAttributeList
    {
        $attributes = array_filter($sourceAttributeList->getAllAttributes(), [$this, 'validateAttributeValue']);
        return new ProductAttributeList(...$attributes);
    }

    private function validateAttributeValue(ProductAttribute $attribute): bool
    {
        if ($attribute->isCodeEqualTo('special_price') && '' === $attribute->getValue()) {
            return false;
        }

        return true;
    }
}
