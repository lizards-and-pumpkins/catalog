<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineFacetFieldCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\PageBuilder;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductInListingSnippetRenderer;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator;

trait ProductListingRequestHandlerTrait
{
    private $paginationQueryParameterName = 'p';

    /**
     * @var Context
     */
    private $context;

    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    /**
     * @var PageBuilder
     */
    private $pageBuilder;

    /**
     * @var SnippetKeyGeneratorLocator
     */
    private $keyGeneratorLocator;

    /**
     * @var string[]
     */
    private $filterNavigationConfig;

    /**
     * @var int
     */
    private $defaultNumberOfProductsPerPage;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param HttpRequest $request
     * @return int
     */
    private function getCurrentPageNumber(HttpRequest $request)
    {
        return max(0, $request->getQueryParameter($this->paginationQueryParameterName) - 1);
    }

    private function addProductListingContentToPage(SearchEngineResponse $searchEngineResponse)
    {
        $searchDocumentCollection = $searchEngineResponse->getSearchDocuments();

        if (0 === count($searchDocumentCollection)) {
            return;
        }

        $facetFieldCollection = $searchEngineResponse->getFacetFieldCollection();

        $this->addFilterNavigationSnippetToPageBuilder($facetFieldCollection);
        $this->addProductsInListingToPageBuilder($searchDocumentCollection);
        $this->addPaginationSnippetsToPageBuilder($searchEngineResponse);
    }

    private function addProductsInListingToPageBuilder(SearchDocumentCollection $searchDocumentCollection)
    {
        $documents = $searchDocumentCollection->getDocuments();
        $productInListingSnippetKeys = $this->getProductInListingSnippetKeysForSearchDocuments(...$documents);
        $productSnippets = $this->dataPoolReader->getSnippets($productInListingSnippetKeys);

        $snippetKey = 'products_grid';
        $snippetContents = '[' . implode(',', $productSnippets) . ']';

        $this->addDynamicSnippetToPageBuilder($snippetKey, $snippetContents);
    }

    /**
     * @param SearchDocument[] $searchDocuments
     * @return string[]
     */
    private function getProductInListingSnippetKeysForSearchDocuments(SearchDocument ...$searchDocuments)
    {
        $keyGenerator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode(
            ProductInListingSnippetRenderer::CODE
        );
        return array_map(function (SearchDocument $searchDocument) use ($keyGenerator) {
            return $keyGenerator->getKeyForContext($this->context, [Product::ID => $searchDocument->getProductId()]);
        }, $searchDocuments);
    }

    private function addFilterNavigationSnippetToPageBuilder(SearchEngineFacetFieldCollection $facetFieldCollection)
    {
        $snippetCode = 'filter_navigation';
        $snippetContents = json_encode($facetFieldCollection, JSON_PRETTY_PRINT);

        $this->addDynamicSnippetToPageBuilder($snippetCode, $snippetContents);
    }

    private function addPaginationSnippetsToPageBuilder(SearchEngineResponse $searchEngineResponse)
    {
        $this->addDynamicSnippetToPageBuilder(
            'total_number_of_results',
            $searchEngineResponse->getTotalNumberOfResults()
        );
        $this->addDynamicSnippetToPageBuilder('products_per_page', (int) $this->defaultNumberOfProductsPerPage);
    }

    /**
     * @param string $snippetCode
     * @param string $snippetContents
     */
    private function addDynamicSnippetToPageBuilder($snippetCode, $snippetContents)
    {
        $snippetCodeToKeyMap = [$snippetCode => $snippetCode];
        $snippetKeyToContentMap = [$snippetCode => $snippetContents];

        $this->pageBuilder->addSnippetsToPage($snippetCodeToKeyMap, $snippetKeyToContentMap);
    }

    /**
     * @param HttpRequest $request
     * @return array[]
     */
    private function getSelectedFilterValuesFromRequest(HttpRequest $request)
    {
        return array_reduce(array_keys($this->filterNavigationConfig), function ($carry, $filterName) use ($request) {
            $carry[$filterName] = array_filter(explode(',', $request->getQueryParameter($filterName)));
            return $carry;
        }, []);
    }
}
