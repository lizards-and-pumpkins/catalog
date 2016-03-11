<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetRenderer;

class ProductListingRobotsMetaTagSnippetRenderer implements SnippetRenderer
{
    const CODE = 'listing_robots_meta_tag';
    
    /**
     * @var RobotsMetaTagSnippetRenderer
     */
    private $robotsMetaTagSnippetRenderer;

    /**
     * @var ContextBuilder
     */
    private $contextBuilder;

    public function __construct(
        RobotsMetaTagSnippetRenderer $robotsMetaTagSnippetRenderer,
        ContextBuilder $contextBuilder
    ) {
        $this->robotsMetaTagSnippetRenderer = $robotsMetaTagSnippetRenderer;
        $this->contextBuilder = $contextBuilder;
    }

    /**
     * @param ProductListing $productListing
     * @return Snippet[]
     */
    public function render(ProductListing $productListing)
    {
        $context = $this->contextBuilder->createContext($productListing->getContextData());
        return $this->robotsMetaTagSnippetRenderer->render($context);
    }
}
