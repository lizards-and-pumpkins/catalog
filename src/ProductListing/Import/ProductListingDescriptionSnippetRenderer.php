<?php

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\SnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\TemplateRendering\ProductListingDescriptionBlockRenderer;

class ProductListingDescriptionSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_listing_description';

    /**
     * @var SnippetKeyGenerator
     */
    private $keyGenerator;

    /**
     * @var ContextBuilder
     */
    private $contextBuilder;

    /**
     * @var ProductListingDescriptionBlockRenderer
     */
    private $blockRenderer;

    public function __construct(
        ProductListingDescriptionBlockRenderer $blockRenderer,
        SnippetKeyGenerator $keyGenerator,
        ContextBuilder $contextBuilder
    ) {
        $this->blockRenderer = $blockRenderer;
        $this->keyGenerator = $keyGenerator;
        $this->contextBuilder = $contextBuilder;
    }

    /**
     * @param ProductListing $productListing
     * @return Snippet[]
     */
    public function render(ProductListing $productListing)
    {
        if (! $productListing->hasAttribute('description')) {
            return [];
        }

        return [$this->createListingDescriptionSnippet($productListing)];
    }

    /**
     * @param ProductListing $productListing
     * @return Snippet
     */
    private function createListingDescriptionSnippet(ProductListing $productListing)
    {
        $context = $this->getContextFromProductListingData($productListing);
        $snippetKeyData = [PageMetaInfoSnippetContent::URL_KEY => $productListing->getUrlKey()];
        $snippetKey = $this->keyGenerator->getKeyForContext($context, $snippetKeyData);
        $snippetContent = $this->blockRenderer->render($productListing, $context);
        
        return Snippet::create($snippetKey, $snippetContent);
    }

    /**
     * @param ProductListing $productListing
     * @return Context
     */
    private function getContextFromProductListingData(ProductListing $productListing)
    {
        $contextData = $productListing->getContextData();
        return $this->contextBuilder->createContext($contextData);
    }
}
