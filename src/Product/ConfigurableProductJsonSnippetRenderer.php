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
        if (! ($product instanceof ConfigurableProduct)) {
            return new SnippetList();
        }
        return $this->createConfigurableProductJsonSnippetList($product);
    }

    /**
     * @param ConfigurableProduct $product
     * @return SnippetList
     */
    private function createConfigurableProductJsonSnippetList(ConfigurableProduct $product)
    {
        $snippetList = new SnippetList();

        $variationAttributesJsonSnippet = $this->createVariationAttributesJsonSnippet($product);
        $snippetList->add($variationAttributesJsonSnippet);
        
        $associatedProductsJsonSnippet = $this->createAssociatedProductsJsonSnippet($product);
        $snippetList->add($associatedProductsJsonSnippet);
        
        return $snippetList;
    }

    /**
     * @param ConfigurableProduct $product
     * @return Snippet
     */
    private function createVariationAttributesJsonSnippet(ConfigurableProduct $product)
    {
        $key = $this->variationAttributesJsonSnippetKeyGenerator->getKeyForContext(
            $product->getContext(),
            ['product_id' => $product->getId()]
        );
        $content = json_encode($product->getVariationAttributes());
        return Snippet::create($key, $content);
    }

    /**
     * @param ConfigurableProduct $product
     * @return Snippet
     */
    private function createAssociatedProductsJsonSnippet(ConfigurableProduct $product)
    {
        $key = $this->associatedProductsJsonSnippetKeyGenerator->getKeyForContext(
            $product->getContext(),
            ['product_id' => $product->getId()]
        );
        $content = $this->getAssociatedProductListJson($product->getAssociatedProducts());
        return Snippet::create($key, $content);
    }

    /**
     * @param AssociatedProductList $associatedProductList
     * @return string
     */
    private function getAssociatedProductListJson(AssociatedProductList $associatedProductList)
    {
        return json_encode($associatedProductList->jsonSerialize()['products']);
    }
}
