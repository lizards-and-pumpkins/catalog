<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\Import\Product\RobotsMetaTagSnippetRenderer;
use LizardsAndPumpkins\Import\SnippetRenderer;

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
    public function render(ProductListing $productListing) : array
    {
        $context = $this->contextBuilder->createContext($productListing->getContextData());
        return $this->robotsMetaTagSnippetRenderer->render($context);
    }
}
