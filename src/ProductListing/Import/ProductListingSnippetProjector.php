<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\Import\Projector;
use LizardsAndPumpkins\Import\SnippetRenderer;
use LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContextCollector;

class ProductListingSnippetProjector implements Projector
{
    /**
     * @var DataPoolWriter
     */
    private $dataPoolWriter;
    
    /**
     * @var UrlKeyForContextCollector
     */
    private $urlKeyForContextCollector;

    /**
     * @var SnippetRenderer[]
     */
    private $snippetRenderers;

    public function __construct(
        UrlKeyForContextCollector $urlKeyForContextCollector,
        DataPoolWriter $dataPoolWriter,
        SnippetRenderer ...$snippetRenderers
    ) {
        $this->dataPoolWriter = $dataPoolWriter;
        $this->urlKeyForContextCollector = $urlKeyForContextCollector;
        $this->snippetRenderers = $snippetRenderers;
    }

    /**
     * @param mixed $projectionSourceData
     */
    public function project($projectionSourceData)
    {
        $this->dataPoolWriter->writeSnippets(...$this->getSnippets($projectionSourceData));

        $urlKeysForContextsCollection = $this->urlKeyForContextCollector->collectListingUrlKeys($projectionSourceData);
        $this->dataPoolWriter->writeUrlKeyCollection($urlKeysForContextsCollection);
    }

    /**
     * @param ProductListing $productListing
     * @return Snippet[]
     */
    private function getSnippets(ProductListing $productListing): array
    {
        return array_map(function (SnippetRenderer $snippetRenderer) use ($productListing) {
            return $snippetRenderer->render($productListing);
        }, $this->snippetRenderers);
    }
}
