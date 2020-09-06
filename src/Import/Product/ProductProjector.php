<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentBuilder;
use LizardsAndPumpkins\Import\Exception\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\Import\Product\View\ProductViewLocator;
use LizardsAndPumpkins\Import\Projector;
use LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContextCollector;

class ProductProjector implements Projector
{
    /**
     * @var Projector
     */
    private $snippetProjector;

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
        Projector $snippetProjector,
        SearchDocumentBuilder $searchDocumentBuilder,
        UrlKeyForContextCollector $urlKeyCollector,
        DataPoolWriter $dataPoolWriter
    ) {
        $this->productViewLocator = $productViewLocator;
        $this->snippetProjector = $snippetProjector;
        $this->searchDocumentBuilder = $searchDocumentBuilder;
        $this->urlKeyCollector = $urlKeyCollector;
        $this->dataPoolWriter = $dataPoolWriter;
    }

    /**
     * @param Product $product
     */
    public function project($product): void
    {
        if (! $product instanceof Product) {
            throw new InvalidProjectionSourceDataTypeException(
                sprintf('Projection source data must be of Product type, got "%s".', typeof($product))
            );
        }

        $productView = $this->productViewLocator->createForProduct($product);

        $this->snippetProjector->project($productView);
        $this->aggregateSearchDocument($product);
        $this->storeProductUrlKeys($product);
    }

    private function aggregateSearchDocument(Product $product): void
    {
        $searchDocument = $this->searchDocumentBuilder->aggregate($product);
        $this->dataPoolWriter->writeSearchDocument($searchDocument);
    }

    private function storeProductUrlKeys(Product $product): void
    {
        $urlKeysForContextsCollection = $this->urlKeyCollector->collectProductUrlKeys($product);
        $this->dataPoolWriter->writeUrlKeyCollection($urlKeysForContextsCollection);
    }
}
