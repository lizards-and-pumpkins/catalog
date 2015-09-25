<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\Projector;
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
        $snippetList = $this->snippetRendererCollection->render($listingCriteria);
        $this->dataPoolWriter->writeSnippetList($snippetList);
        
        $urlKeysForContextsCollection = $this->urlKeyForContextCollector->collectListingUrlKeys($listingCriteria);
        $this->dataPoolWriter->writeUrlKeyCollection($urlKeysForContextsCollection);
    }
}
