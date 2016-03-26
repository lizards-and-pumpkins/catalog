<?php

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\SnippetRenderer;

class TwentyOneRunProductListingTitleSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_listing_title';

    const TITLE_SUFFIX = ' | 21run.com';

    /**
     * @var SnippetKeyGenerator
     */
    private $keyGenerator;

    /**
     * @var ContextBuilder
     */
    private $contextBuilder;

    public function __construct(SnippetKeyGenerator $keyGenerator, ContextBuilder $contextBuilder)
    {
        $this->keyGenerator = $keyGenerator;
        $this->contextBuilder = $contextBuilder;
    }

    /**
     * @param ProductListing $productListing
     * @return Snippet[]
     */
    public function render(ProductListing $productListing)
    {
        if (!$productListing->hasAttribute('meta_title')) {
            return [];
        }

        $context = $this->contextBuilder->createContext($productListing->getContextData());
        $contextData = [PageMetaInfoSnippetContent::URL_KEY => $productListing->getUrlKey()];
        $key = $this->keyGenerator->getKeyForContext($context, $contextData);
        $content = $productListing->getAttributeValueByCode('meta_title') . self::TITLE_SUFFIX;

        return [Snippet::create($key, $content)];
    }
}
