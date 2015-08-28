<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\DataPool\DataPoolWriter;
use Brera\InvalidProjectionSourceDataTypeException;
use Brera\Projector;
use Brera\SnippetRendererCollection;

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
