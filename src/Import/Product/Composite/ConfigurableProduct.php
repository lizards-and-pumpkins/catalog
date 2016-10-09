<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\Composite;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\ProductAttributeList;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\Import\Product\Image\ProductImage;
use LizardsAndPumpkins\Import\Product\Image\ProductImageList;
use LizardsAndPumpkins\Import\Product\RehydrateableProductTrait;
use LizardsAndPumpkins\Import\Product\SimpleProduct;
use LizardsAndPumpkins\Import\Product\Composite\Exception\ConfigurableProductAssociatedProductListInvariantViolationException;
use LizardsAndPumpkins\Import\Tax\ProductTaxClass;

class ConfigurableProduct implements CompositeProduct
{
    use RehydrateableProductTrait;
    
    const SIMPLE_PRODUCT = 'simple_product';
    const TYPE_CODE = 'configurable';
    const VARIATION_ATTRIBUTES = 'variation_attributes';
    const ASSOCIATED_PRODUCTS = 'associated_products';

    /**
     * @var SimpleProduct
     */
    private $simpleProductDelegate;

    /**
     * @var ProductVariationAttributeList
     */
    private $variationAttributes;
    
    /**
     * @var AssociatedProductList
     */
    private $associatedProducts;

    public function __construct(
        SimpleProduct $simpleProduct,
        ProductVariationAttributeList $variationAttributes,
        AssociatedProductList $associatedProducts
    ) {
        $this->validate($simpleProduct, $variationAttributes, $associatedProducts);
        $this->simpleProductDelegate = $simpleProduct;
        $this->variationAttributes = $variationAttributes;
        $this->associatedProducts = $associatedProducts;
    }

    private function validate(
        SimpleProduct $simpleProduct,
        ProductVariationAttributeList $variationAttributes,
        AssociatedProductList $associatedProducts
    ) {
        try {
            $associatedProducts->validateUniqueValueCombinationForEachProductAttribute(
                ...$variationAttributes->getAttributes()
            );
        } catch (AssociatedProductListDomainException $e) {
            $message = sprintf('Invalid configurable product "%s": %s', $simpleProduct->getId(), $e->getMessage());
            throw new ConfigurableProductAssociatedProductListInvariantViolationException($message);
        }
    }

    /**
     * @param mixed[] $sourceArray
     * @return ConfigurableProduct
     */
    public static function fromArray(array $sourceArray) : ConfigurableProduct
    {
        self::validateTypeCodeInSourceArray(self::TYPE_CODE, $sourceArray);
        return new self(
            SimpleProduct::fromArray($sourceArray[self::SIMPLE_PRODUCT]),
            ProductVariationAttributeList::fromArray(...$sourceArray[self::VARIATION_ATTRIBUTES]),
            AssociatedProductList::fromArray($sourceArray[self::ASSOCIATED_PRODUCTS])
        );
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize() : array
    {
        return [
            Product::TYPE_KEY          => self::TYPE_CODE,
            self::SIMPLE_PRODUCT       => $this->simpleProductDelegate->jsonSerialize(),
            self::VARIATION_ATTRIBUTES => $this->variationAttributes->jsonSerialize(),
            self::ASSOCIATED_PRODUCTS  => $this->associatedProducts->jsonSerialize(),
        ];
    }

    public function getId() : ProductId
    {
        return $this->simpleProductDelegate->getId();
    }

    /**
     * @param string $attributeCode
     * @return mixed
     */
    public function getFirstValueOfAttribute(string $attributeCode)
    {
        return $this->simpleProductDelegate->getFirstValueOfAttribute($attributeCode);
    }

    /**
     * @param string $attributeCode
     * @return string[]
     */
    public function getAllValuesOfAttribute(string $attributeCode) : array
    {
        return $this->simpleProductDelegate->getAllValuesOfAttribute($attributeCode);
    }

    public function hasAttribute(AttributeCode $attributeCode) : bool
    {
        return $this->simpleProductDelegate->hasAttribute($attributeCode);
    }

    public function getAttributes() : ProductAttributeList
    {
        return $this->simpleProductDelegate->getAttributes();
    }

    public function getContext() : Context
    {
        return $this->simpleProductDelegate->getContext();
    }

    public function getImages() : ProductImageList
    {
        return $this->simpleProductDelegate->getImages();
    }

    public function getImageCount() : int
    {
        return $this->simpleProductDelegate->getImageCount();
    }

    public function getImageByNumber(int $imageNumber) : ProductImage
    {
        return $this->simpleProductDelegate->getImageByNumber($imageNumber);
    }

    public function getImageFileNameByNumber(int $imageNumber) : string
    {
        return $this->simpleProductDelegate->getImageFileNameByNumber($imageNumber);
    }

    public function getImageLabelByNumber(int $imageNumber) : string
    {
        return $this->simpleProductDelegate->getImageLabelByNumber($imageNumber);
    }

    public function getMainImageFileName() : string
    {
        return $this->simpleProductDelegate->getMainImageFileName();
    }

    public function getMainImageLabel() : string
    {
        return $this->simpleProductDelegate->getMainImageLabel();
    }

    public function getVariationAttributes() : ProductVariationAttributeList
    {
        return $this->variationAttributes;
    }

    public function getAssociatedProducts() : AssociatedProductList
    {
        return $this->associatedProducts;
    }

    public function getTaxClass() : ProductTaxClass
    {
        return $this->simpleProductDelegate->getTaxClass();
    }
}
