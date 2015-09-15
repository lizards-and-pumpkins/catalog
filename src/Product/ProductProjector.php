<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\Projection\UrlKeyForContextCollector;
use LizardsAndPumpkins\Projector;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\SnippetRendererCollection;

class ProductProjector implements Projector
{
    /**
     * @var SnippetRendererCollection
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
     * @var UrlKeyForContextCollector
     */
    private $urlKeyCollector;

    public function __construct(
        SnippetRendererCollection $rendererCollection,
        ProductSearchDocumentBuilder $searchDocumentBuilder,
        UrlKeyForContextCollector $urlKeyCollector,
        DataPoolWriter $dataPoolWriter
    ) {
        $this->rendererCollection = $rendererCollection;
        $this->searchDocumentBuilder = $searchDocumentBuilder;
        $this->urlKeyCollector = $urlKeyCollector;
        $this->dataPoolWriter = $dataPoolWriter;
    }

    /**
     * @param mixed $projectionSourceData
     * @param ContextSource $contextSource
     */
    public function project($projectionSourceData, ContextSource $contextSource)
    {
        if (!($projectionSourceData instanceof ProductSource)) {
            throw new InvalidProjectionSourceDataTypeException('First argument must be instance of ProductSource.');
        }

        $this->projectProduct($projectionSourceData, $contextSource);
    }

    private function projectProduct(ProductSource $productSource, ContextSource $contextSource)
    {
        $snippetList = $this->rendererCollection->render($productSource, $contextSource);
        $this->dataPoolWriter->writeSnippetList($snippetList);

        $searchDocumentCollection = $this->searchDocumentBuilder->aggregate($productSource, $contextSource);
        $this->dataPoolWriter->writeSearchDocumentCollection($searchDocumentCollection);
        
        $urlKeyCollection = $this->urlKeyCollector->collectProductUrlKeys($productSource, $contextSource);
        $this->dataPoolWriter->writeUrlCollection($urlKeyCollection);
    }
}
