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
use LizardsAndPumpkins\Product\PriceSnippetRenderer;
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
     * @var ProductsPerPage
     */
    private $productsPerPage;

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

    private function addProductListingContentToPage(
        SearchEngineResponse $searchEngineResponse,
        ProductsPerPage $productsPerPage
    ) {
        $searchDocumentCollection = $searchEngineResponse->getSearchDocuments();

        if (0 === count($searchDocumentCollection)) {
            return;
        }

        $facetFieldCollection = $searchEngineResponse->getFacetFieldCollection();

        $this->addFilterNavigationSnippetToPageBuilder($facetFieldCollection);
        $this->addProductsInListingToPageBuilder($searchDocumentCollection);
        $this->addPaginationSnippetsToPageBuilder($searchEngineResponse, $productsPerPage);
    }

    private function addProductsInListingToPageBuilder(SearchDocumentCollection $searchDocumentCollection)
    {
        $documents = $searchDocumentCollection->getDocuments();
        $productInListingSnippetKeys = $this->getProductInListingSnippetKeysForSearchDocuments(...$documents);
        $productPriceSnippetKeys = $this->getProductPriceSnippetKeysForSearchDocuments(...$documents);

        $snippetKeysToFetch = array_merge($productInListingSnippetKeys, $productPriceSnippetKeys);
        $snippets = $this->dataPoolReader->getSnippets($snippetKeysToFetch);

        $this->addProductGridSnippetToPageBuilder($snippets, $productInListingSnippetKeys);
        $this->addProductPricesSnippetToPageBuilder($snippets, $productPriceSnippetKeys);
    }

    /**
     * @param SearchDocument[] $searchDocuments
     * @return string[]
     */
    private function getProductInListingSnippetKeysForSearchDocuments(SearchDocument ...$searchDocuments)
    {
        return $this->getSnippetKeysForGivenSnippetCode(ProductInListingSnippetRenderer::CODE, ...$searchDocuments);
    }

    /**
     * @param SearchDocument[] $searchDocuments
     * @return string[]
     */
    private function getProductPriceSnippetKeysForSearchDocuments(SearchDocument ...$searchDocuments)
    {
        return $this->getSnippetKeysForGivenSnippetCode(PriceSnippetRenderer::CODE, ...$searchDocuments);
    }

    /**
     * @param string $snippetCode
     * @param SearchDocument[] $searchDocuments
     * @return string[]
     */
    private function getSnippetKeysForGivenSnippetCode($snippetCode, SearchDocument ...$searchDocuments)
    {
        $keyGenerator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode($snippetCode);
        return array_map(function (SearchDocument $searchDocument) use ($keyGenerator) {
            return $keyGenerator->getKeyForContext($this->context, [Product::ID => $searchDocument->getProductId()]);
        }, $searchDocuments);
    }

    /**
     * @param string[] $snippets
     * @param string[] $productInListingSnippetKeys
     */
    private function addProductGridSnippetToPageBuilder($snippets, $productInListingSnippetKeys)
    {
        $this->addCombinedSnippetsWithGivenKeysToPageBuilder($snippets, $productInListingSnippetKeys, 'product_grid');
    }

    /**
     * @param string[] $snippets
     * @param string[] $productPriceSnippetKeys
     */
    private function addProductPricesSnippetToPageBuilder($snippets, $productPriceSnippetKeys)
    {
        $this->addCombinedSnippetsWithGivenKeysToPageBuilder($snippets, $productPriceSnippetKeys, 'product_prices');
    }

    /**
     * @param string[] $allSnippets
     * @param string[] $snippetKeysToUse
     * @param string $combinedSnippetKey
     */
    private function addCombinedSnippetsWithGivenKeysToPageBuilder($allSnippets, $snippetKeysToUse, $combinedSnippetKey)
    {
        $matchingSnippets = array_intersect_key($allSnippets, array_flip($snippetKeysToUse));
        $combinedSnippetContent = '[' . implode(',', $matchingSnippets) . ']';
        $this->addDynamicSnippetToPageBuilder($combinedSnippetKey, $combinedSnippetContent);
    }

    private function addFilterNavigationSnippetToPageBuilder(SearchEngineFacetFieldCollection $facetFieldCollection)
    {
        $snippetCode = 'filter_navigation';
        $snippetContents = json_encode($facetFieldCollection, JSON_PRETTY_PRINT);

        $this->addDynamicSnippetToPageBuilder($snippetCode, $snippetContents);
    }

    private function addPaginationSnippetsToPageBuilder(
        SearchEngineResponse $searchEngineResponse,
        ProductsPerPage $productsPerPage
    ) {
        $this->addDynamicSnippetToPageBuilder(
            'total_number_of_results',
            $searchEngineResponse->getTotalNumberOfResults()
        );
        $this->addDynamicSnippetToPageBuilder(
            'products_per_page',
            $productsPerPage->getSelectedNumberOfProductsPerPage()
        );
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

    /**
     * @param HttpRequest $request
     * @return ProductsPerPage
     */
    private function getProductsPerPage(HttpRequest $request)
    {
        if ($request->hasCookie(ProductListingRequestHandler::PRODUCTS_PER_PAGE_COOKIE_NAME) === true) {
            $numbersOfProductsPerPage = $this->productsPerPage->getNumbersOfProductsPerPage();
            $selected = $request->getCookieValue(ProductListingRequestHandler::PRODUCTS_PER_PAGE_COOKIE_NAME);
            return ProductsPerPage::create($numbersOfProductsPerPage, $selected);
        }

        return $this->productsPerPage;
    }
}
