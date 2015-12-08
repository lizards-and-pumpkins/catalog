<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Projection\Catalog\InternalToPublicProductJsonData;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetList;
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
     * @param Product $product
     * @return SnippetList
     */
    public function render(Product $product)
    {
        $snippetList = new SnippetList();

        $variationAttributesJsonSnippet = $this->createVariationAttributesJsonSnippet($product);
        $snippetList->add($variationAttributesJsonSnippet);

        $associatedProductsJsonSnippet = $this->createAssociatedProductsJsonSnippet($product);
        $snippetList->add($associatedProductsJsonSnippet);

        return $snippetList;
    }

    /**
     * @param Product $product
     * @return bool
     */
    private function isCompositeProduct(Product $product)
    {
        return $product instanceof CompositeProduct;
    }

    /**
     * @param Product $product
     * @return Snippet
     */
    private function createVariationAttributesJsonSnippet(Product $product)
    {
        $key = $this->variationAttributesJsonSnippetKeyGenerator->getKeyForContext(
            $product->getContext(),
            ['product_id' => $product->getId()]
        );
        return Snippet::create($key, $this->createVariationAttributesJsonSnippetContent($product));
    }

    /**
     * @param Product $product
     * @return string
     */
    private function createVariationAttributesJsonSnippetContent(Product $product)
    {
        if ($this->isCompositeProduct($product)) {
            /** @var CompositeProduct $product */
            return json_encode($this->getVariationAttributesJsonData($product));
        }

        return json_encode([]);
    }

    /**
     * @param CompositeProduct $product
     * @return string[]
     */
    private function getVariationAttributesJsonData(CompositeProduct $product)
    {
        $variationAttributesJson = json_encode($product->getVariationAttributes());
        return $this->internalToPublicProductJsonData->transformVariationAttributes(
            json_decode($variationAttributesJson, true)
        );
    }

    /**
     * @param Product $product
     * @return Snippet
     */
    private function createAssociatedProductsJsonSnippet(Product $product)
    {
        $key = $this->associatedProductsJsonSnippetKeyGenerator->getKeyForContext(
            $product->getContext(),
            ['product_id' => $product->getId()]
        );
        return Snippet::create($key, $this->createAssociatedProductsJsonSnippetContent($product));
    }

    /**
     * @param Product $product
     * @return string
     */
    private function createAssociatedProductsJsonSnippetContent(Product $product)
    {
        if ($this->isCompositeProduct($product)) {
            /** @var CompositeProduct $product */
            return json_encode($this->getAssociatedProductListJson($product));
        }

        return json_encode([]);
    }

    /**
     * @param CompositeProduct $product
     * @return array[]
     */
    private function getAssociatedProductListJson(CompositeProduct $product)
    {
        $associatedProductListJson = json_encode($product->getAssociatedProducts());
        return $this->internalToPublicProductJsonData->transformAssociatedProducts(
            json_decode($associatedProductListJson, true)
        );
    }
}
