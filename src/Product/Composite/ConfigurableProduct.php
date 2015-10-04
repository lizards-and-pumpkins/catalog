<?php


namespace LizardsAndPumpkins\Product\Composite;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\Composite\Exception\AssociatedProductListDomainException;
use LizardsAndPumpkins\Product\Exception\ProductTypeCodeMismatchException;
use LizardsAndPumpkins\Product\Exception\ProductTypeCodeMissingException;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Product\ProductImage;
use LizardsAndPumpkins\Product\ProductImageList;
use LizardsAndPumpkins\Product\SimpleProduct;
use LizardsAndPumpkins\Product\Composite\Exception\ConfigurableProductAssociatedProductListInvariantViolationException;

class ConfigurableProduct implements Product
{
    const TYPE_CODE = 'configurable';
    
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
        self::validateSourceArray($sourceArray);
        return new self(
            SimpleProduct::fromArray($sourceArray['simple_product']),
            ProductVariationAttributeList::fromArray($sourceArray['variation_attributes']),
            AssociatedProductList::fromArray($sourceArray['associated_products'])
        );
    }

    /**
     * @param mixed[] $sourceArray
     */
    private static function validateSourceArray(array $sourceArray)
    {
        if (! isset($sourceArray[Product::TYPE_KEY])) {
            $message = sprintf('The array key "%s" is missing from source array', Product::TYPE_KEY);
            throw new ProductTypeCodeMissingException($message);
        }
        if (self::TYPE_CODE !== $sourceArray[Product::TYPE_KEY]) {
            $variableType = self::getStringRepresentation($sourceArray[Product::TYPE_KEY]);
            $message = sprintf('Expected the product type code string "%s", got "%s"', self::TYPE_CODE, $variableType);
            throw new ProductTypeCodeMismatchException($message);
        }
    }

    /**
     * @param mixed $variable
     * @return string
     */
    private static function getStringRepresentation($variable)
    {
        if (is_string($variable)) {
            return $variable;
        }
        return is_object($variable) ?
            get_class($variable) :
            gettype($variable);
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize()
    {
        return [
            Product::TYPE_KEY => self::TYPE_CODE,
            'simple_product' => $this->simpleProductDelegate,
            'variation_attributes' => $this->variationAttributes,
            'associated_products' => $this->associatedProducts,
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
}
