<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Product\Composite\AssociatedProductList;
use LizardsAndPumpkins\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Product\Composite\ProductVariationAttributeList;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductAttribute;
use LizardsAndPumpkins\Product\ProductAttributeList;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Product\ProductImage;
use LizardsAndPumpkins\Product\ProductImageList;
use LizardsAndPumpkins\Product\Tax\ProductTaxClass;

class TwentyOneRunConfigurableProductView implements CompositeProductView
{
    const MAX_PURCHASABLE_QTY = 5;

    /**
     * @var ConfigurableProduct
     */
    private $product;

    /**
     * @var ProductAttributeList
     */
    private $memoizedProductAttributesList;

    public function __construct(ConfigurableProduct $product)
    {
        $this->product = $product;
    }

    /**
     * @return Product
     */
    public function getOriginalProduct()
    {
        return $this->product;
    }

    /**
     * @return ProductId
     */
    public function getId()
    {
        return $this->product->getId();
    }

    /**
     * @param string $attributeCode
     * @return string
     */
    public function getFirstValueOfAttribute($attributeCode)
    {
        $attributeValues = $this->getAllValuesOfAttribute($attributeCode);

        if (empty($attributeValues)) {
            return '';
        }

        return $attributeValues[0];
    }

    /**
     * @param string $attributeCode
     * @return string[]
     */
    public function getAllValuesOfAttribute($attributeCode)
    {
        $attributeList = $this->getAttributes();

        if (!$attributeList->hasAttribute($attributeCode)) {
            return [];
        }

        return array_map(function (ProductAttribute $productAttribute) {
            return $productAttribute->getValue();
        }, $attributeList->getAttributesWithCode($attributeCode));
    }

    /**
     * @param string $attributeCode
     * @return bool
     */
    public function hasAttribute($attributeCode)
    {
        return $this->getAttributes()->hasAttribute($attributeCode);
    }

    /**
     * @return ProductAttributeList
     */
    public function getAttributes()
    {
        if (null === $this->memoizedProductAttributesList) {
            $originalAttributes = $this->product->getAttributes();
            $this->memoizedProductAttributesList = $this->filterProductAttributeList($originalAttributes);
        }

        return $this->memoizedProductAttributesList;
    }


    /**
     * @return Context
     */
    public function getContext()
    {
        return $this->product->getContext();
    }

    /**
     * @return ProductImageList
     */
    public function getImages()
    {
        return $this->product->getImages();
    }

    /**
     * @return int
     */
    public function getImageCount()
    {
        return $this->product->getImageCount();
    }

    /**
     * @param int $imageNumber
     * @return ProductImage
     */
    public function getImageByNumber($imageNumber)
    {
        return $this->product->getImageByNumber($imageNumber);
    }

    /**
     * @param int $imageNumber
     * @return string
     */
    public function getImageFileNameByNumber($imageNumber)
    {
        return $this->product->getImageFileNameByNumber($imageNumber);
    }

    /**
     * @param int $imageNumber
     * @return string
     */
    public function getImageLabelByNumber($imageNumber)
    {
        return $this->product->getImageLabelByNumber($imageNumber);
    }

    /**
     * @return string
     */
    public function getMainImageFileName()
    {
        return $this->product->getMainImageFileName();
    }

    /**
     * @return string
     */
    public function getMainImageLabel()
    {
        return $this->product->getMainImageLabel();
    }

    /**
     * @return ProductTaxClass
     */
    public function getTaxClass()
    {
        return $this->product->getTaxClass();
    }

    public function jsonSerialize()
    {
        $productData = $this->product->jsonSerialize();
        $productData['attributes'] = $this->getAttributes();

        return $productData;
    }

    /**
     * @return ProductVariationAttributeList
     */
    public function getVariationAttributes()
    {
        return $this->product->getVariationAttributes();
    }

    /**
     * @return AssociatedProductList
     */
    public function getAssociatedProducts()
    {
        return $this->product->getAssociatedProducts();
    }

    /**
     * @param ProductAttributeList $attributeList
     * @return ProductAttributeList
     */
    private function filterProductAttributeList(ProductAttributeList $attributeList)
    {
        $attributeCodesToBeRemoved = ['price', 'special_price', 'backorders'];

        $filteredAttributes = array_reduce(
            $attributeList->getAllAttributes(),
            function (array $carry, ProductAttribute $attribute) use ($attributeCodesToBeRemoved) {
                if (!in_array((string) $attribute->getCode(), $attributeCodesToBeRemoved)) {
                    $carry[] = $attribute;
                }

                return $carry;
            },
            []
        );

        return new ProductAttributeList(...$filteredAttributes);
    }
}
