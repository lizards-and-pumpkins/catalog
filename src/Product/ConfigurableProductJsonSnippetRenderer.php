<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Projection\Catalog\CompositeProductView;
use LizardsAndPumpkins\Projection\Catalog\InternalToPublicProductJsonData;
use LizardsAndPumpkins\Projection\Catalog\ProductView;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetRenderer;

class ConfigurableProductJsonSnippetRenderer implements SnippetRenderer
{
    const VARIATION_ATTRIBUTES_CODE = 'configurable_product_variation_attributes';
    const ASSOCIATED_PRODUCTS_CODE = 'configurable_product_associated_products';

    /**
     * @var SnippetKeyGenerator
     */
    private $variationAttributesJsonSnippetKeyGenerator;

    /**
     * @var SnippetKeyGenerator
     */
    private $associatedProductsJsonSnippetKeyGenerator;

    /**
     * @var InternalToPublicProductJsonData
     */
    private $internalToPublicProductJsonData;

    public function __construct(
        SnippetKeyGenerator $variationAttributesJsonSnippetKeyGenerator,
        SnippetKeyGenerator $associatedProductsJsonSnippetKeyGenerator,
        InternalToPublicProductJsonData $internalToPublicProductJsonData
    ) {
        $this->variationAttributesJsonSnippetKeyGenerator = $variationAttributesJsonSnippetKeyGenerator;
        $this->associatedProductsJsonSnippetKeyGenerator = $associatedProductsJsonSnippetKeyGenerator;
        $this->internalToPublicProductJsonData = $internalToPublicProductJsonData;
    }

    /**
     * @param ProductView $product
     * @return Snippet[]
     */
    public function render(ProductView $product)
    {
        return [
            $this->createVariationAttributesJsonSnippet($product),
            $this->createAssociatedProductsJsonSnippet($product)
        ];
    }

    /**
     * @param ProductView $productView
     * @return bool
     */
    private function isCompositeProduct(ProductView $productView)
    {
        return $productView instanceof CompositeProductView;
    }

    /**
     * @param ProductView $product
     * @return Snippet
     */
    private function createVariationAttributesJsonSnippet(ProductView $product)
    {
        $key = $this->variationAttributesJsonSnippetKeyGenerator->getKeyForContext(
            $product->getContext(),
            ['product_id' => $product->getId()]
        );
        return Snippet::create($key, $this->createVariationAttributesJsonSnippetContent($product));
    }

    /**
     * @param ProductView $product
     * @return string
     */
    private function createVariationAttributesJsonSnippetContent(ProductView $product)
    {
        if ($this->isCompositeProduct($product)) {
            return json_encode($this->getVariationAttributesJsonData($product));
        }

        return json_encode([]);
    }

    /**
     * @param CompositeProductView $product
     * @return string[]
     */
    private function getVariationAttributesJsonData(CompositeProductView $product)
    {
        $variationAttributesJson = json_encode($product->getVariationAttributes());
        return $this->internalToPublicProductJsonData->transformVariationAttributes(
            json_decode($variationAttributesJson, true)
        );
    }

    /**
     * @param ProductView $product
     * @return Snippet
     */
    private function createAssociatedProductsJsonSnippet(ProductView $product)
    {
        $key = $this->associatedProductsJsonSnippetKeyGenerator->getKeyForContext(
            $product->getContext(),
            ['product_id' => $product->getId()]
        );
        return Snippet::create($key, $this->createAssociatedProductsJsonSnippetContent($product));
    }

    /**
     * @param ProductView $product
     * @return string
     */
    private function createAssociatedProductsJsonSnippetContent(ProductView $product)
    {
        if ($this->isCompositeProduct($product)) {
            return json_encode($this->getAssociatedProductListJson($product));
        }

        return json_encode([]);
    }

    /**
     * @param CompositeProductView $product
     * @return array[]
     */
    private function getAssociatedProductListJson(CompositeProductView $product)
    {
        $associatedProductListJson = json_encode($product->getAssociatedProducts());
        return $this->internalToPublicProductJsonData->transformAssociatedProducts(
            json_decode($associatedProductListJson, true)
        );
    }
}
