<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentBuilder;
use LizardsAndPumpkins\Projection\Catalog\ProductViewLocator;
use LizardsAndPumpkins\Projection\Projector;
use LizardsAndPumpkins\Projection\UrlKeyForContextCollector;
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

    /**
     * @var ProductViewLocator
     */
    private $productViewLocator;

    public function __construct(
        ProductViewLocator $productViewLocator,
        SnippetRendererCollection $rendererCollection,
        SearchDocumentBuilder $searchDocumentBuilder,
        UrlKeyForContextCollector $urlKeyCollector,
        DataPoolWriter $dataPoolWriter
    ) {
        $this->productViewLocator = $productViewLocator;
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
        $productView = $this->productViewLocator->createForProduct($projectionSourceData);

        $this->projectProduct($productView);
        $this->aggregateSearchDocuments($projectionSourceData);
        $this->storeProductUrlKeys($productView);
    }

    private function projectProduct(Product $product)
    {
        $snippetList = $this->rendererCollection->render($product);
        $this->dataPoolWriter->writeSnippetList($snippetList);
    }

    /**
     * @param Product $product
     */
    private function aggregateSearchDocuments(Product $product)
    {
        $searchDocumentCollection = $this->searchDocumentBuilder->aggregate($product);
        $this->dataPoolWriter->writeSearchDocumentCollection($searchDocumentCollection);
    }

    /**
     * @param Product $product
     */
    private function storeProductUrlKeys(Product $product)
    {
        $urlKeysForContextsCollection = $this->urlKeyCollector->collectProductUrlKeys($product);
        $this->dataPoolWriter->writeUrlKeyCollection($urlKeysForContextsCollection);
    }
}
