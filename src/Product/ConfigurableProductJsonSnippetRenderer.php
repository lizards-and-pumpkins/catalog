<?php


namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Product\Composite\AssociatedProductList;
use LizardsAndPumpkins\Product\Composite\ConfigurableProduct;
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

    public function __construct(
        SnippetKeyGenerator $variationAttributesJsonSnippetKeyGenerator,
        SnippetKeyGenerator $associatedProductsJsonSnippetKeyGenerator
    ) {

        $this->variationAttributesJsonSnippetKeyGenerator = $variationAttributesJsonSnippetKeyGenerator;
        $this->associatedProductsJsonSnippetKeyGenerator = $associatedProductsJsonSnippetKeyGenerator;
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
    private function isConfigurableProduct(Product $product)
    {
        return $product instanceof ConfigurableProduct;
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
        $content = $this->isConfigurableProduct($product) ?
            $product->getVariationAttributes() :
            [];
        return json_encode($content);
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
        $content = $this->isConfigurableProduct($product) ?
            $this->getAssociatedProductListJson($product->getAssociatedProducts()) :
            [];
        return json_encode($content);
    }

    /**
     * @param AssociatedProductList $associatedProductList
     * @return string
     */
    private function getAssociatedProductListJson(AssociatedProductList $associatedProductList)
    {
        return $associatedProductList->jsonSerialize()['products'];
    }
}
