<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentBuilder;
use LizardsAndPumpkins\Import\Product\View\ProductView;
use LizardsAndPumpkins\Import\Product\View\ProductViewLocator;
use LizardsAndPumpkins\Import\Projector;
use LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContextCollector;
use LizardsAndPumpkins\Import\SnippetRendererCollection;

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

    /**
     * @var ProductDomainModelBuilder
     */
    private $productDomainModelBuilder;

    public function __construct(
        ProductViewLocator $productViewLocator,
        SnippetRendererCollection $rendererCollection,
        SearchDocumentBuilder $searchDocumentBuilder,
        UrlKeyForContextCollector $urlKeyCollector,
        DataPoolWriter $dataPoolWriter,
        ProductDomainModelBuilder $productDomainModelBuilder
    ) {
        $this->productViewLocator = $productViewLocator;
        $this->rendererCollection = $rendererCollection;
        $this->searchDocumentBuilder = $searchDocumentBuilder;
        $this->urlKeyCollector = $urlKeyCollector;
        $this->dataPoolWriter = $dataPoolWriter;
        $this->productDomainModelBuilder = $productDomainModelBuilder;
    }

    /**
     * @param ProductDTO $productDTO
     */
    public function project($productDTO)
    {
        $productView = $this->productViewLocator->createForProduct($productDTO);
        $productDomainModel = $this->productDomainModelBuilder->create($productDTO);

        $this->projectProductView($productView);
        $this->aggregateSearchDocument($productDomainModel);
        $this->storeProductUrlKeys($productDomainModel);
    }

    private function projectProductView(ProductView $product)
    {
        $snippets = $this->rendererCollection->render($product);
        $this->dataPoolWriter->writeSnippets(...$snippets);
    }

    private function aggregateSearchDocument(ProductDTO $product)
    {
        $searchDocument = $this->searchDocumentBuilder->aggregate($product);
        $this->dataPoolWriter->writeSearchDocument($searchDocument);
    }

    private function storeProductUrlKeys(ProductDTO $product)
    {
        $urlKeysForContextsCollection = $this->urlKeyCollector->collectProductUrlKeys($product);
        $this->dataPoolWriter->writeUrlKeyCollection($urlKeysForContextsCollection);
    }
}
