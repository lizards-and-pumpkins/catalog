<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentBuilder;
use LizardsAndPumpkins\Import\Product\View\ProductView;
use LizardsAndPumpkins\Import\Product\View\ProductViewLocator;
use LizardsAndPumpkins\Import\Projector;
use LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContextCollector;
use LizardsAndPumpkins\Import\SnippetRenderer;

class ProductProjector implements Projector
{
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

    /**
     * @var SnippetRenderer[]
     */
    private $snippetRenderers;

    public function __construct(
        ProductViewLocator $productViewLocator,
        SearchDocumentBuilder $searchDocumentBuilder,
        UrlKeyForContextCollector $urlKeyCollector,
        DataPoolWriter $dataPoolWriter,
        SnippetRenderer ...$snippetRenderers
    ) {
        $this->productViewLocator = $productViewLocator;
        $this->searchDocumentBuilder = $searchDocumentBuilder;
        $this->urlKeyCollector = $urlKeyCollector;
        $this->dataPoolWriter = $dataPoolWriter;
        $this->snippetRenderers = $snippetRenderers;
    }

    /**
     * @param Product $product
     */
    public function project($product)
    {
        $productView = $this->productViewLocator->createForProduct($product);

        $this->projectProductView($productView);
        $this->aggregateSearchDocument($product);
        $this->storeProductUrlKeys($product);
    }

    private function projectProductView(ProductView $product)
    {
        $this->dataPoolWriter->writeSnippets(...$this->getSnippets($product));
    }

    /**
     * @param ProductView $product
     * @return Snippet[]
     */
    private function getSnippets(ProductView $product): array
    {
        return array_map(function (SnippetRenderer $snippetRenderer) use ($product) {
            return $snippetRenderer->render($product);
        }, $this->snippetRenderers);
    }

    private function aggregateSearchDocument(Product $product)
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
