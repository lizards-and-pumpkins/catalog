<?php

namespace Brera\Product;

use Brera\DataPool\DataPoolWriter;
use Brera\Projector;
use Brera\ProjectionSourceData;
use Brera\Environment\EnvironmentSource;
use Brera\InvalidProjectionDataSourceType;

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
     * @param EnvironmentSource $environmentSource
     * @return void
     * @throws InvalidProjectionDataSourceType
     */
    public function project(ProjectionSourceData $productSource, EnvironmentSource $environmentSource)
    {
        if (!($productSource instanceof ProductSource)) {
            throw new InvalidProjectionDataSourceType('First argument must be instance of ProductSource.');
        }

        $this->projectProduct($productSource, $environmentSource);
    }

    /**
     * @param ProductSource $productSource
     * @param EnvironmentSource $environmentSource
     * @return void
     */
    private function projectProduct(ProductSource $productSource, EnvironmentSource $environmentSource)
    {
        $snippetResultList = $this->rendererCollection->render($productSource, $environmentSource);
        $this->dataPoolWriter->writeSnippetResultList($snippetResultList);

        $searchDocument = $this->searchDocumentBuilder->aggregate($productSource, $environmentSource);
        $this->dataPoolWriter->writeSearchDocumentCollection($searchDocument);
    }
}
