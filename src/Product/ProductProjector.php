<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentBuilder;
use LizardsAndPumpkins\Projection\Catalog\ProductView;
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
     * @param Product $product
     */
    public function project($product)
    {
        $productView = $this->productViewLocator->createForProduct($product);

        $this->projectProductView($productView);
        $this->aggregateSearchDocuments($product);
        $this->storeProductUrlKeys($product);
    }

    private function projectProductView(ProductView $product)
    {
        $snippets = $this->rendererCollection->render($product);
        $this->dataPoolWriter->writeSnippets(...$snippets);
    }

    private function aggregateSearchDocuments(Product $product)
    {
        $searchDocument = $this->searchDocumentBuilder->aggregate($product);
        $this->dataPoolWriter->writeSearchDocument($searchDocument);
    }

    private function storeProductUrlKeys(Product $product)
    {
        $urlKeysForContextsCollection = $this->urlKeyCollector->collectProductUrlKeys($product);
        $this->dataPoolWriter->writeUrlKeyCollection($urlKeysForContextsCollection);
    }
}
