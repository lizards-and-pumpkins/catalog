<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\Projector;
use LizardsAndPumpkins\SnippetRendererCollection;
use LizardsAndPumpkins\Projection\UrlKeyForContextCollector;

class ProductListingMetaInfoSnippetProjector implements Projector
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
     * @param ContextSource $contextSource
     */
    public function project($projectionSourceData, ContextSource $contextSource)
    {
        if (!($projectionSourceData instanceof ProductListingMetaInfo)) {
            throw new InvalidProjectionSourceDataTypeException(
                'First argument must be instance of ProductListingMetaInfo.'
            );
        }

        $this->projectProductListing($projectionSourceData, $contextSource);
    }

    private function projectProductListing(
        ProductListingMetaInfo $productListingMetaInfo,
        ContextSource $contextSource
    ) {
        $snippetList = $this->snippetRendererCollection->render($productListingMetaInfo, $contextSource);
        $this->dataPoolWriter->writeSnippetList($snippetList);
        
        $urlKeysInContextsCollection = $this->urlKeyForContextCollector->collectListingUrlKeys(
            $productListingMetaInfo,
            $contextSource
        );
        $this->dataPoolWriter->writeUrlKeyCollection($urlKeysInContextsCollection);
    }
}
