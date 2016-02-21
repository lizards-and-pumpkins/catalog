<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetRenderer;

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

    public function __construct(
        SnippetKeyGenerator $keyGenerator,
        ContextBuilder $contextBuilder
    ) {
        $this->keyGenerator = $keyGenerator;
        $this->contextBuilder = $contextBuilder;
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
        $snippetKey = $this->keyGenerator->getKeyForContext(
            $this->getContextFromProductListingData($productListing),
            [PageMetaInfoSnippetContent::URL_KEY => $productListing->getUrlKey()]
        );
        $snippetContent = $productListing->getAttributeValueByCode('description');
        return Snippet::create($snippetKey, $snippetContent);
    }
}
