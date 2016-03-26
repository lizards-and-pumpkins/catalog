<?php

namespace LizardsAndPumpkins\ProductDetail\Import;

use LizardsAndPumpkins\Import\Product\View\CompositeProductView;
use LizardsAndPumpkins\Import\Product\View\ProductView;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\SnippetRenderer;

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

    public function __construct(
        SnippetKeyGenerator $variationAttributesJsonSnippetKeyGenerator,
        SnippetKeyGenerator $associatedProductsJsonSnippetKeyGenerator
    ) {
        $this->variationAttributesJsonSnippetKeyGenerator = $variationAttributesJsonSnippetKeyGenerator;
        $this->associatedProductsJsonSnippetKeyGenerator = $associatedProductsJsonSnippetKeyGenerator;
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
        return json_decode(json_encode($product->getVariationAttributes()), true);
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
        return $product->getAssociatedProducts();
    }
}
