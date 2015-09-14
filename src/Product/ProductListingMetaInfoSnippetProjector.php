<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\Projector;
use LizardsAndPumpkins\SnippetRendererCollection;

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

    public function __construct(SnippetRendererCollection $snippetRendererCollection, DataPoolWriter $dataPoolWriter)
    {
        $this->snippetRendererCollection = $snippetRendererCollection;
        $this->dataPoolWriter = $dataPoolWriter;
    }

    /**
     * @param mixed $projectionSourceData
     * @param ContextSource $contextSource
     */
    public function project($projectionSourceData, ContextSource $contextSource)
    {
        if (!($projectionSourceData instanceof ProductListingMetaInfoSource)) {
            throw new InvalidProjectionSourceDataTypeException(
                'First argument must be instance of ProductListingMetaInfoSource.'
            );
        }

        $this->projectProductListing($projectionSourceData, $contextSource);
    }

    private function projectProductListing(
        ProductListingMetaInfoSource $productListingMetaInfoSource,
        ContextSource $contextSource
    ) {
        $snippetList = $this->snippetRendererCollection->render($productListingMetaInfoSource, $contextSource);
        $this->dataPoolWriter->writeSnippetList($snippetList);
    }
}
