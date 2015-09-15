<?php


namespace LizardsAndPumpkins\Projection;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductSource;
use LizardsAndPumpkins\UrlKey;

class UrlKeyForContextCollector
{
    /**
     * @param ProductSource $productSource
     * @param ContextSource $contextSource
     * @return UrlKeyForContextCollection
     */
    public function collectProductUrlKeys(ProductSource $productSource, ContextSource $contextSource)
    {
        $urlKeysForContext = $this->getUrlKeyForContexts($productSource, $contextSource->getAllAvailableContexts());
        return new UrlKeyForContextCollection(...$urlKeysForContext);
    }

    /**
     * @param ProductSource $productSource
     * @param Context[] $contexts
     * @return UrlKeyForContext[]
     */
    private function getUrlKeyForContexts(ProductSource $productSource, array $contexts)
    {
        return array_map(function (Context $context) use ($productSource) {
            $product = $productSource->getProductForContext($context);
            return $this->getProductUrlKeyForContext($product, $context);
        }, $contexts);
    }

    /**
     * @param Product $product
     * @param Context $context
     * @return UrlKeyForContext
     */
    private function getProductUrlKeyForContext(Product $product, Context $context)
    {
        $urlKey = $product->getFirstValueOfAttribute(Product::URL_KEY);
        return new UrlKeyForContext(UrlKey::fromString($urlKey), $context);
    }
}
