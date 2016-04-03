<?php

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

    /**
     * @param Context $context
     * @return bool
     */
    public function isAvailableForContext(Context $context)
    {
        $sourceAttributeList = $this->attributeListBuilder->getAttributeListForContext($context);
        return count($sourceAttributeList) > 0;
    }

    /**
     * @param Context $context
     * @return Product
     */
    public function getProductForContext(Context $context)
    {
        $sourceAttributeList = $this->attributeListBuilder->getAttributeListForContext($context);
        $attributesWithProperTypes = $this->ensureAttributeTypes($sourceAttributeList);
        $images = $this->imageListBuilder->getImageListForContext($context);
        return new SimpleProduct($this->id, $this->taxClass, $attributesWithProperTypes, $images, $context);
    }

    /**
     * @param ProductAttributeList $sourceAttributeList
     * @return ProductAttributeList
     */
    private function ensureAttributeTypes(ProductAttributeList $sourceAttributeList)
    {
        $attributes = array_map([$this, 'ensureAttributeType'], $sourceAttributeList->getAllAttributes());
        return new ProductAttributeList(...$attributes);
    }


    /**
     * @param ProductAttribute $attribute
     * @return ProductAttribute
     */
    private function ensureAttributeType(ProductAttribute $attribute)
    {
        if ($attribute->isCodeEqualTo('price') || $attribute->isCodeEqualTo('special_price')) {
            return $this->ensurePriceAttributeTypeInt($attribute);
        }
        return $attribute;
    }

    /**
     * @param ProductAttribute $attribute
     * @return ProductAttribute
     */
    private function ensurePriceAttributeTypeInt(ProductAttribute $attribute)
    {
        if (is_int($attribute->getValue())) {
            return $attribute;
        }
        $price = Price::fromDecimalValue($attribute->getValue());
        return new ProductAttribute($attribute->getCode(), $price->getAmount(), $attribute->getContextDataSet());
    }
}
