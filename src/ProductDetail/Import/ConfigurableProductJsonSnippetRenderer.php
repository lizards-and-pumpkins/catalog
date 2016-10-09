<?php

declare(strict_types=1);

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
    public function render(ProductView $product) : array
    {
        return [
            $this->createVariationAttributesJsonSnippet($product),
            $this->createAssociatedProductsJsonSnippet($product)
        ];
    }

    private function isCompositeProduct(ProductView $productView) : bool
    {
        return $productView instanceof CompositeProductView;
    }

    private function createVariationAttributesJsonSnippet(ProductView $product) : Snippet
    {
        $key = $this->variationAttributesJsonSnippetKeyGenerator->getKeyForContext(
            $product->getContext(),
            ['product_id' => $product->getId()]
        );
        return Snippet::create($key, $this->createVariationAttributesJsonSnippetContent($product));
    }

    private function createVariationAttributesJsonSnippetContent(ProductView $product) : string
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
    private function getVariationAttributesJsonData(CompositeProductView $product) : array
    {
        return json_decode(json_encode($product->getVariationAttributes()), true);
    }

    private function createAssociatedProductsJsonSnippet(ProductView $product) : Snippet
    {
        $key = $this->associatedProductsJsonSnippetKeyGenerator->getKeyForContext(
            $product->getContext(),
            ['product_id' => $product->getId()]
        );
        return Snippet::create($key, $this->createAssociatedProductsJsonSnippetContent($product));
    }

    private function createAssociatedProductsJsonSnippetContent(ProductView $product) : string
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
    private function getAssociatedProductListJson(CompositeProductView $product) : array
    {
        return $product->getAssociatedProducts();
    }
}
