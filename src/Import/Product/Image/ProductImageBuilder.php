<?php

namespace LizardsAndPumpkins\Import\Product\Image;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Product\ProductAttribute;
use LizardsAndPumpkins\Import\Product\ProductAttributeListBuilder;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\Import\Product\Image\ProductImage;
use LizardsAndPumpkins\Import\Product\Image\InvalidProductImageAttributeListException;

class ProductImageBuilder
{
    /**
     * @var ProductAttributeListBuilder
     */
    private $attributeListBuilder;

    public function __construct(ProductAttributeListBuilder $attributeListBuilder)
    {
        $this->attributeListBuilder = $attributeListBuilder;
    }

    /**
     * @param ProductId $productId
     * @param array[] $imageAttributesArray
     * @return ProductImageBuilder
     */
    public static function fromArray(ProductId $productId, array $imageAttributesArray)
    {
        self::validateImageAttributesArray($productId, $imageAttributesArray);
        return new self(ProductAttributeListBuilder::fromArray($imageAttributesArray));
    }

    /**
     * @param ProductId $productId
     * @param array[] $imageAttributesArray
     */
    private static function validateImageAttributesArray(ProductId $productId, array $imageAttributesArray)
    {
        self::validateItHasGivenImageAttribute($productId, $imageAttributesArray, ProductImage::FILE);
    }

    /**
     * @param ProductId $productId
     * @param array[] $imageAttributesArray
     * @param string $code
     */
    private static function validateItHasGivenImageAttribute(ProductId $productId, array $imageAttributesArray, $code)
    {
        if (!self::isAttributeInArray($code, $imageAttributesArray)) {
            $message = sprintf('The image attribute "%s" is missing for product "%s"', $code, $productId);
            throw new InvalidProductImageAttributeListException($message);
        }
    }

    /**
     * @param string $code
     * @param array[] $attributesArray
     * @return bool
     */
    private static function isAttributeInArray($code, array $attributesArray)
    {
        return array_reduce($attributesArray, function ($found, array $attribute) use ($code) {
            return $found || isset($attribute[ProductAttribute::CODE]) && $attribute[ProductAttribute::CODE] === $code;
        }, false);
    }

    /**
     * @param Context $context
     * @return ProductImage
     */
    public function getImageForContext(Context $context)
    {
        $attributes = $this->attributeListBuilder->getAttributeListForContext($context);
        return new ProductImage($attributes);
    }
}
