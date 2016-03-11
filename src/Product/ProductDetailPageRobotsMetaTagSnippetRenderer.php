<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Projection\Catalog\ProductView;
use LizardsAndPumpkins\Snippet;
use LizardsAndPumpkins\SnippetRenderer;

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
    public function render(ProductView $productView)
    {
        return $this->robotsMetaTagRenderer->render($productView->getContext());
    }
}
