<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\DataPool\DataPoolWriter;
use Brera\InvalidProjectionDataSourceTypeException;
use Brera\ProjectionSourceData;
use Brera\Projector;
use Brera\SnippetRendererCollection;

class ProductListingProjector implements Projector
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
     * @param ProjectionSourceData $dataObject
     * @param ContextSource $contextSource
     */
    public function project(ProjectionSourceData $dataObject, ContextSource $contextSource)
    {
        if (!($dataObject instanceof ProductListingSource)) {
            throw new InvalidProjectionDataSourceTypeException(
                'First argument must be instance of ProductListingSource.'
            );
        }

        $this->projectProductListing($dataObject, $contextSource);
    }

    private function projectProductListing(ProductListingSource $productListingSource, ContextSource $contextSource)
    {
        $snippetList = $this->snippetRendererCollection->render($productListingSource, $contextSource);
        $this->dataPoolWriter->writeSnippetList($snippetList);
    }
}
