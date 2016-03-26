<?php

namespace LizardsAndPumpkins\Import\Product\Composite;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\Product\Composite\AssociatedProductListDomainException;
use LizardsAndPumpkins\Import\Product\Composite\CompositeProduct;
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
    public static function fromArray(array $sourceArray)
    {
        self::validateTypeCodeInSourceArray(self::TYPE_CODE, $sourceArray);
        return new self(
            SimpleProduct::fromArray($sourceArray[self::SIMPLE_PRODUCT]),
            ProductVariationAttributeList::fromArray($sourceArray[self::VARIATION_ATTRIBUTES]),
            AssociatedProductList::fromArray($sourceArray[self::ASSOCIATED_PRODUCTS])
        );
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize()
    {
        return [
            Product::TYPE_KEY          => self::TYPE_CODE,
            self::SIMPLE_PRODUCT       => $this->simpleProductDelegate->jsonSerialize(),
            self::VARIATION_ATTRIBUTES => $this->variationAttributes->jsonSerialize(),
            self::ASSOCIATED_PRODUCTS  => $this->associatedProducts->jsonSerialize(),
        ];
    }

    /**
     * @return ProductId
     */
    public function getId()
    {
        return $this->simpleProductDelegate->getId();
    }

    /**
     * @param string $attributeCode
     * @return string
     */
    public function getFirstValueOfAttribute($attributeCode)
    {
        return $this->simpleProductDelegate->getFirstValueOfAttribute($attributeCode);
    }

    /**
     * @param string $attributeCode
     * @return string[]
     */
    public function getAllValuesOfAttribute($attributeCode)
    {
        return $this->simpleProductDelegate->getAllValuesOfAttribute($attributeCode);
    }

    /**
     * @param string $attributeCode
     * @return bool
     */
    public function hasAttribute($attributeCode)
    {
        return $this->simpleProductDelegate->hasAttribute($attributeCode);
    }

    /**
     * @return ProductAttributeList
     */
    public function getAttributes()
    {
        return $this->simpleProductDelegate->getAttributes();
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->simpleProductDelegate->getContext();
    }

    /**
     * @return ProductImageList
     */
    public function getImages()
    {
        return $this->simpleProductDelegate->getImages();
    }

    /**
     * @return int
     */
    public function getImageCount()
    {
        return $this->simpleProductDelegate->getImageCount();
    }

    /**
     * @param int $imageNumber
     * @return ProductImage
     */
    public function getImageByNumber($imageNumber)
    {
        return $this->simpleProductDelegate->getImageByNumber($imageNumber);
    }

    /**
     * @param int $imageNumber
     * @return string
     */
    public function getImageFileNameByNumber($imageNumber)
    {
        return $this->simpleProductDelegate->getImageFileNameByNumber($imageNumber);
    }

    /**
     * @param int $imageNumber
     * @return string
     */
    public function getImageLabelByNumber($imageNumber)
    {
        return $this->simpleProductDelegate->getImageLabelByNumber($imageNumber);
    }

    /**
     * @return string
     */
    public function getMainImageFileName()
    {
        return $this->simpleProductDelegate->getMainImageFileName();
    }

    /**
     * @return string
     */
    public function getMainImageLabel()
    {
        return $this->simpleProductDelegate->getMainImageLabel();
    }

    /**
     * @return ProductVariationAttributeList
     */
    public function getVariationAttributes()
    {
        return $this->variationAttributes;
    }

    /**
     * @return AssociatedProductList
     */
    public function getAssociatedProducts()
    {
        return $this->associatedProducts;
    }

    /**
     * @return ProductTaxClass
     */
    public function getTaxClass()
    {
        return $this->simpleProductDelegate->getTaxClass();
    }
}
