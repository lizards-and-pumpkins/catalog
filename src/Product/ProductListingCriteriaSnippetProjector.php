<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\Exception\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\Projection\Projector;
use LizardsAndPumpkins\SnippetRendererCollection;
use LizardsAndPumpkins\Projection\UrlKeyForContextCollector;

class ProductListingCriteriaSnippetProjector implements Projector
{
    /**
     * @var SnippetRendererCollection
     */
    private $snippetRendererCollection;

    /**
     * @var DataPoolWriter
     */
    private $dataPoolWriter;
    
    /**
     * @var UrlKeyForContextCollector
     */
    private $urlKeyForContextCollector;

    public function __construct(
        SnippetRendererCollection $snippetRendererCollection,
        UrlKeyForContextCollector $urlKeyForContextCollector,
        DataPoolWriter $dataPoolWriter
    ) {
        $this->snippetRendererCollection = $snippetRendererCollection;
        $this->dataPoolWriter = $dataPoolWriter;
        $this->urlKeyForContextCollector = $urlKeyForContextCollector;
    }

    /**
     * @param mixed $projectionSourceData
     */
    public function project($projectionSourceData)
    {
        if (!($projectionSourceData instanceof ProductListingCriteria)) {
            throw new InvalidProjectionSourceDataTypeException(
                'First argument must be instance of ProductListingMetaInfo.'
            );
        }

        $this->projectProductListing($projectionSourceData);
    }

    private function projectProductListing(ProductListingCriteria $listingCriteria)
    {
        $snippets = $this->snippetRendererCollection->render($listingCriteria);
        $this->dataPoolWriter->writeSnippets(...$snippets);
        
        $urlKeysForContextsCollection = $this->urlKeyForContextCollector->collectListingUrlKeys($listingCriteria);
        $this->dataPoolWriter->writeUrlKeyCollection($urlKeysForContextsCollection);
    }
}
