<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentBuilder;
use LizardsAndPumpkins\Projection\UrlKeyForContextCollector;
use LizardsAndPumpkins\Projector;
use LizardsAndPumpkins\Exception\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\SnippetRendererCollection;

class ProductProjector implements Projector
{
    /**
     * @var SnippetRendererCollection
     */
    private $rendererCollection;

    /**
     * @var SearchDocumentBuilder
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
        SearchDocumentBuilder $searchDocumentBuilder,
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
     */
    public function project($projectionSourceData)
    {
        if (!($projectionSourceData instanceof Product)) {
            throw new InvalidProjectionSourceDataTypeException('First argument must be a Product instance.');
        }

        $this->projectProduct($projectionSourceData);
    }

    private function projectProduct(Product $product)
    {
        $snippetList = $this->rendererCollection->render($product);
        $this->dataPoolWriter->writeSnippetList($snippetList);

        $searchDocumentCollection = $this->searchDocumentBuilder->aggregate($product);
        $this->dataPoolWriter->writeSearchDocumentCollection($searchDocumentCollection);
        
        $urlKeysForContextsCollection = $this->urlKeyCollector->collectProductUrlKeys($product);
        $this->dataPoolWriter->writeUrlKeyCollection($urlKeysForContextsCollection);
    }
}
