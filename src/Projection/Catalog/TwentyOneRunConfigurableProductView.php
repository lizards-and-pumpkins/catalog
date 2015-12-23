<?php

namespace LizardsAndPumpkins\Projection\Catalog;

use LizardsAndPumpkins\Product\Composite\AssociatedProductList;
use LizardsAndPumpkins\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Product\Composite\ProductVariationAttributeList;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductAttribute;
use LizardsAndPumpkins\Product\ProductAttributeList;
use LizardsAndPumpkins\Product\ProductImage\ProductImage;
use LizardsAndPumpkins\Product\ProductImage\ProductImageFileLocator;
use LizardsAndPumpkins\Product\ProductImage\TwentyOneRunProductImageFileLocator;

class TwentyOneRunConfigurableProductView extends AbstractProductView implements CompositeProductView
{
    const MAX_PURCHASABLE_QTY = 5;

    /**
     * @var ProductViewLocator
     */
    private $productViewLocator;

    /**
     * @var ConfigurableProduct
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

    public function __construct(
        ProductViewLocator $productViewLocator,
        ConfigurableProduct $product,
        ProductImageFileLocator $productImageFileLocator
    ) {
        $this->productViewLocator = $productViewLocator;
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
     * @return ProductVariationAttributeList
     */
    public function getVariationAttributes()
    {
        return $this->product->getVariationAttributes();
    }

    /**
     * @return ProductView[]
     */
    public function getAssociatedProducts()
    {
        return array_map(function (Product $associatedProduct) {
            return $this->productViewLocator->createForProduct($associatedProduct);
        }, $this->product->getAssociatedProducts()->getProducts());
    }

    /**
     * @param ProductAttributeList $attributeList
     * @return ProductAttributeList
     */
    private function filterProductAttributeList(ProductAttributeList $attributeList)
    {
        $filteredAttributes = $this->removeScreenedAttributes($attributeList);

        return new ProductAttributeList(...$filteredAttributes);
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
     * @return ProductImageFileLocator
     */
    protected function getProductImageFileLocator()
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
