<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductDetail;

use LizardsAndPumpkins\Import\Product\RobotsMetaTagSnippetRenderer;
use LizardsAndPumpkins\Import\Product\View\ProductView;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\Import\SnippetRenderer;

class ProductDetailPageRobotsMetaTagSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_page_robots_tag';
    
    /**
     * @var RobotsMetaTagSnippetRenderer
     */
    private $robotsMetaTagRenderer;

    public function __construct(RobotsMetaTagSnippetRenderer $robotsMetaTagRenderer)
    {
        $this->robotsMetaTagRenderer = $robotsMetaTagRenderer;
    }

    /**
     * @param ProductView $productView
     * @return Snippet[]
     */
    public function render(ProductView $productView) : array
    {
        return $this->robotsMetaTagRenderer->render($productView->getContext());
    }
}
