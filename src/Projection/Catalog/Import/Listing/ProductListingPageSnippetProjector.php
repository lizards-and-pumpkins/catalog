<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import\Listing;

use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\DataVersion;

class ProductListingPageSnippetProjector
{
    /**
     * @var DataPoolWriter
     */
    private $dataPoolWriter;
    
    /**
     * @var ContextSource
     */
    private $contextSource;

    /**
     * @var ProductListingPageSnippetRenderer
     */
    private $productListingPageSnippetRenderer;

    public function __construct(
        ProductListingPageSnippetRenderer $productListingPageSnippetRenderer,
        DataPoolWriter $dataPoolWriter,
        ContextSource $contextSource
    ) {
        $this->productListingPageSnippetRenderer = $productListingPageSnippetRenderer;
        $this->dataPoolWriter = $dataPoolWriter;
        $this->contextSource = $contextSource;
    }
    
    public function project(DataVersion $dataVersion)
    {
        $allAvailableContextsForVersion = $this->contextSource->getAllAvailableContextsWithVersion($dataVersion);
        foreach ($allAvailableContextsForVersion as $context) {
            $snippet = $this->productListingPageSnippetRenderer->render($context);
            $this->dataPoolWriter->writeSnippet($snippet);
        }
    }
}
