<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\Product\Price;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductAttribute;
use LizardsAndPumpkins\Product\ProductAttributeList;
use LizardsAndPumpkins\Product\SimpleProduct;
use LizardsAndPumpkins\Product\ProductId;

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

    public function __construct(
        ProductId $id,
        ProductAttributeListBuilder $attributeListBuilder,
        ProductImageListBuilder $imageListBuilder
    ) {
        $this->id = $id;
        $this->attributeListBuilder = $attributeListBuilder;
        $this->imageListBuilder = $imageListBuilder;
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
        return new SimpleProduct($this->id, $attributesWithProperTypes, $images, $context);
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
        $price = Price::fromString($attribute->getValue());
        return new ProductAttribute($attribute->getCode(), $price->getAmount(), $attribute->getContextDataSet());
    }
}
