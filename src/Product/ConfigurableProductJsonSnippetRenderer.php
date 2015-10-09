<?php


namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Product\Composite\AssociatedProductList;
use LizardsAndPumpkins\Product\Composite\ConfigurableProduct;
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
            $this->getVariationAttributesJsonData($product) :
            [];
        return json_encode($content);
    }

    /**
     * @param ConfigurableProduct $product
     * @return string[]
     */
    private function getVariationAttributesJsonData(ConfigurableProduct $product)
    {
        $variationAttributesJsonData = $product->getVariationAttributes()->jsonSerialize();
        return $this->internalToPublicProductJsonData->transformVariationAttributes($variationAttributesJsonData);
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
            $this->getAssociatedProductListJson($product) :
            [];
        return json_encode($content);
    }

    /**
     * @param ConfigurableProduct $product
     * @return array[]
     */
    private function getAssociatedProductListJson(ConfigurableProduct $product)
    {
        $associatedProductListJsonData = $product->getAssociatedProducts()->jsonSerialize();
        return $this->internalToPublicProductJsonData->transformAssociatedProducts($associatedProductListJsonData);
    }
}
