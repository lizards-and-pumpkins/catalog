<?php

namespace LizardsAndPumpkins\ProductListing;

use LizardsAndPumpkins\Context\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Import\SnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;

class ProductListingCanonicalTagSnippetRenderer implements SnippetRenderer
{
    /**
     * @var SnippetKeyGenerator
     */
    private $snippetKeyGenerator;

    /**
     * @var BaseUrlBuilder
     */
    private $baseUrlBuilder;

    /**
     * @var ContextBuilder
     */
    private $contextBuilder;

    public function __construct(
        SnippetKeyGenerator $snippetKeyGenerator,
        BaseUrlBuilder $baseUrlBuilder,
        ContextBuilder $contextBuilder
    ) {
        $this->snippetKeyGenerator = $snippetKeyGenerator;
        $this->baseUrlBuilder = $baseUrlBuilder;
        $this->contextBuilder = $contextBuilder;
    }

    /**
     * @param ProductListing $productListing
     * @return string
     */
    public function render(ProductListing $productListing)
    {
        $key = $this->getProductListingCanonicalTagSnippetKey($productListing);
        $content = $this->createProductListingCanonicalTag($productListing);

        return [Snippet::create($key, $content)];

    }

    private function getProductListingCanonicalTagSnippetKey(ProductListing $productListing)
    {
        $productListingUrlKey = $productListing->getUrlKey();
        return $this->snippetKeyGenerator->getKeyForContext(
            $this->getContextFromProductListingData($productListing),
            [PageMetaInfoSnippetContent::URL_KEY => $productListingUrlKey]
        );
    }

    /**
     * @param ProductListing $productListing
     * @return string
     */
    private function createProductListingCanonicalTag(ProductListing $productListing)
    {
        $baseUrl = $this->baseUrlBuilder->create($this->getContextFromProductListingData($productListing));
        $urlKey = $productListing->getUrlKey();

        return sprintf('<link rel="canonical" href="%s%s" />', $baseUrl, $urlKey);
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
