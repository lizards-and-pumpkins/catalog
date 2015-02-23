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

    /**
     * @param ProductSnippetRendererCollection $rendererCollection
     * @param ProductSearchDocumentBuilder $searchDocumentBuilder
     * @param DataPoolWriter $dataPoolWriter
     */
    public function __construct(
        ProductSnippetRendererCollection $rendererCollection,
        ProductSearchDocumentBuilder $searchDocumentBuilder,
        DataPoolWriter $dataPoolWriter
    )
    {
        $this->rendererCollection = $rendererCollection;
        $this->searchDocumentBuilder = $searchDocumentBuilder;
        $this->dataPoolWriter = $dataPoolWriter;
    }

    /**
     * @param ProductSource|ProjectionSourceData $productSource
     * @param ContextSource $contextSource
     * @return void
     * @throws InvalidProjectionDataSourceTypeException
     */
    public function project(ProjectionSourceData $productSource, ContextSource $contextSource)
    {
        if (!($productSource instanceof ProductSource)) {
            throw new InvalidProjectionDataSourceTypeException('First argument must be instance of ProductSource.');
        }

        $this->projectProduct($productSource, $contextSource);
    }

    /**
     * @param ProductSource $productSource
     * @param ContextSource $contextSource
     * @return void
     */
    private function projectProduct(ProductSource $productSource, ContextSource $contextSource)
    {
        $snippetResultList = $this->rendererCollection->render($productSource, $contextSource);
        $this->dataPoolWriter->writeSnippetResultList($snippetResultList);

        $searchDocument = $this->searchDocumentBuilder->aggregate($productSource, $contextSource);
        $this->dataPoolWriter->writeSearchDocumentCollection($searchDocument);
    }
}
