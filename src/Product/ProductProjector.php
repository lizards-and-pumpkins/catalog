<?php

namespace Brera\Product;

use Brera\DataPool\DataPoolWriter;
use Brera\Projector;
use Brera\ProjectionSourceData;
use Brera\Context\ContextSource;
use Brera\InvalidProjectionDataSourceTypeException;

class ProductProjector implements Projector
{
    /**
     * @var ProductSnippetRendererCollection
     */
    private $rendererCollection;

    /**
     * @var ProductSearchDocumentBuilder
     */
    private $searchDocumentBuilder;

    /**
     * @var DataPoolWriter
     */
    private $dataPoolWriter;

    public function __construct(
        ProductSnippetRendererCollection $rendererCollection,
        ProductSearchDocumentBuilder $searchDocumentBuilder,
        DataPoolWriter $dataPoolWriter
    ) {
        $this->rendererCollection = $rendererCollection;
        $this->searchDocumentBuilder = $searchDocumentBuilder;
        $this->dataPoolWriter = $dataPoolWriter;
    }

    /**
     * @param ProductSource|ProjectionSourceData $productSource
     * @param ContextSource $contextSource
     * @throws InvalidProjectionDataSourceTypeException
     */
    public function project(ProjectionSourceData $productSource, ContextSource $contextSource)
    {
        if (!($productSource instanceof ProductSource)) {
            throw new InvalidProjectionDataSourceTypeException('First argument must be instance of ProductSource.');
        }

        $this->projectProduct($productSource, $contextSource);
    }

    private function projectProduct(ProductSource $productSource, ContextSource $contextSource)
    {
        $snippetList = $this->rendererCollection->render($productSource, $contextSource);
        $this->dataPoolWriter->writeSnippetList($snippetList);

        $searchDocumentCollection = $this->searchDocumentBuilder->aggregate($productSource, $contextSource);
        $this->dataPoolWriter->writeSearchDocumentCollection($searchDocumentCollection);
    }
}
