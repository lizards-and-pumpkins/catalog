<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductAttribute;
use LizardsAndPumpkins\Product\ProductAttributeList;
use LizardsAndPumpkins\Product\ProductImage\ProductImage;
use LizardsAndPumpkins\Product\ProductImage\ProductImageFileLocator;
use LizardsAndPumpkins\Product\ProductImage\TwentyOneRunProductImageFileLocator;

class TwentyOneRunSimpleProductView extends AbstractProductView
{
    const MAX_PURCHASABLE_QTY = 5;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var ProductAttributeList
     */
    private $memoizedProductAttributesList;

    /**
     * @var ProductImageFileLocator
     */
    private $productImageFileLocator;

    public function __construct(Product $product, ProductImageFileLocator $productImageFileLocator)
    {
        $this->product = $product;
        $this->productImageFileLocator = $productImageFileLocator;
    }

    /**
     * {@inheritdoc}
     */
    public function getOriginalProduct()
    {
        return $this->product;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function hasAttribute($attributeCode)
    {
        return $this->getAttributes()->hasAttribute($attributeCode);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        $productData = $this->product->jsonSerialize();
        $productData['attributes'] = $this->getAttributes();

        unset($productData['images']);
        $productData['images'] = $this->getAllProductImageUrls();

        return $productData;
    }

    /**
     * @param ProductAttributeList $attributeList
     * @return ProductAttributeList
     */
    private function filterProductAttributeList(ProductAttributeList $attributeList)
    {
        $filteredAttributes = $this->removeScreenedAttributes($attributeList);
        $attributesWithProcessedStockQty = $this->processStockQtyAttribute($filteredAttributes);

        return new ProductAttributeList(...$attributesWithProcessedStockQty);
    }

    /**
     * @param ProductAttribute $stockQty
     * @return ProductAttribute
     */
    private function getBoundedStockQtyAttribute(ProductAttribute $stockQty)
    {
        if ($this->isOverMaxQtyToShow($stockQty) || $this->hasBackorders()) {
            return $this->createStockQtyAttributeAtMaximumPurchasableLevel($stockQty);
        }

        return $stockQty;
    }

    /**
     * @param ProductAttribute $stockQty
     * @return bool
     */
    private function isOverMaxQtyToShow(ProductAttribute $stockQty)
    {
        return $stockQty->getValue() > self::MAX_PURCHASABLE_QTY;
    }

    /**
     * @return bool
     */
    private function hasBackorders()
    {
        return $this->product->getFirstValueOfAttribute('backorders') === 'true';
    }

    /**
     * @param ProductAttribute $attribute
     * @return ProductAttribute
     */
    private function createStockQtyAttributeAtMaximumPurchasableLevel(ProductAttribute $attribute)
    {
        return new ProductAttribute('stock_qty', self::MAX_PURCHASABLE_QTY, $attribute->getContextDataSet());
    }

    /**
     * @param ProductAttributeList $attributeList
     * @return ProductAttribute[]
     */
    private function removeScreenedAttributes(ProductAttributeList $attributeList)
    {
        $attributeCodesToBeRemoved = ['price', 'special_price', 'backorders'];
        $attributes = $attributeList->getAllAttributes();

        return array_filter($attributes, function (ProductAttribute $attribute) use ($attributeCodesToBeRemoved) {
            return !in_array((string) $attribute->getCode(), $attributeCodesToBeRemoved);
        });
    }

    /**
     * @param ProductAttribute[] $filteredAttributes
     * @return ProductAttribute[]
     */
    private function processStockQtyAttribute(array $filteredAttributes)
    {
        return array_map(function (ProductAttribute $attribute) {
            if ($attribute->getCode() == 'stock_qty') {
                return $this->getBoundedStockQtyAttribute($attribute);
            }
            return $attribute;
        }, $filteredAttributes);
    }

    /**
     * @return ProductImageFileLocator
     */
    final protected function getProductImageFileLocator()
    {
        return $this->productImageFileLocator;
    }

    /**
     * @return array[]
     */
    private function getAllProductImageUrls()
    {
        $imageUrls = [];
        foreach ([
                     TwentyOneRunProductImageFileLocator::ORIGINAL,
                     TwentyOneRunProductImageFileLocator::LARGE,
                     TwentyOneRunProductImageFileLocator::MEDIUM,
                     TwentyOneRunProductImageFileLocator::SMALL,
                     TwentyOneRunProductImageFileLocator::SEARCH_AUTOSUGGESTION,
                 ] as $variantCode) {
            $imageUrls[$variantCode] = array_map(function (ProductImage $productImage) use ($variantCode) {
                $context = $this->getContext();
                $image = $this->productImageFileLocator->get($productImage->getFileName(), $variantCode, $context);
                return ['url' => (string) $image->getUrl($context), 'label' => $productImage->getLabel()];
            }, iterator_to_array($this->product->getImages()));
            if (count($imageUrls[$variantCode]) === 0) {
                $placeholder = $this->productImageFileLocator->getPlaceholder($variantCode, $this->getContext());
                $imageUrls[$variantCode][] = [
                    'url' => $placeholder->getUrl($this->getContext()),
                    'label' => ''
                ];
            }
        };
        return $imageUrls;
    }
}
