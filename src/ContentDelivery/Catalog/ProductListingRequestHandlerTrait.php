<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\ContentDelivery\Catalog\Exception\NoSelectedSortOrderException;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineFacetFieldCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\PageBuilder;
use LizardsAndPumpkins\Product\AttributeCode;
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
     * @var SortOrderConfig[]
     */
    private $sortOrderConfigs;

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
        ProductsPerPage $productsPerPage,
        SortOrderConfig $selectedSortOrderConfig
    ) {
        $searchDocumentCollection = $searchEngineResponse->getSearchDocuments();

        if (0 === count($searchDocumentCollection)) {
            return;
        }

        $facetFieldCollection = $searchEngineResponse->getFacetFieldCollection();

        $this->addFilterNavigationSnippetToPageBuilder($facetFieldCollection);
        $this->addProductsInListingToPageBuilder($searchDocumentCollection);
        $this->addPaginationSnippetsToPageBuilder($searchEngineResponse, $productsPerPage);
        $this->addSortOrderSnippetToPageBuilder($selectedSortOrderConfig);
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
        $this->addDynamicSnippetToPageBuilder('products_per_page', json_encode($productsPerPage));
    }

    private function addSortOrderSnippetToPageBuilder(SortOrderConfig $selectedSortOrderConfig)
    {
        $sortOrderConfig = $this->getSortOrderConfigsWithGivenConfigSelected($selectedSortOrderConfig);
        $this->addDynamicSnippetToPageBuilder('sort_order_config', json_encode($sortOrderConfig));
    }

    /**
     * @param SortOrderConfig $selectedSortOrderConfig
     * @return SortOrderConfig[]
     */
    private function getSortOrderConfigsWithGivenConfigSelected(SortOrderConfig $selectedSortOrderConfig)
    {
        return array_map(function (SortOrderConfig $sortOrderConfig) use ($selectedSortOrderConfig) {
            if ($sortOrderConfig->getAttributeCode() == $selectedSortOrderConfig->getAttributeCode()) {
                return $selectedSortOrderConfig;
            }

            if ($sortOrderConfig->isSelected() === true) {
                return SortOrderConfig::create(
                    $sortOrderConfig->getAttributeCode(),
                    $sortOrderConfig->getSelectedDirection()
                );
            }

            return $sortOrderConfig;
        }, $this->sortOrderConfigs);
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
        $productsPerPageQueryStringValue = $this->getProductsPerPageQueryStringValue($request);
        if (null !== $productsPerPageQueryStringValue) {
            $numbersOfProductsPerPage = $this->productsPerPage->getNumbersOfProductsPerPage();
            return ProductsPerPage::create($numbersOfProductsPerPage, (int) $productsPerPageQueryStringValue);
        }

        if ($request->hasCookie(ProductListingRequestHandler::PRODUCTS_PER_PAGE_COOKIE_NAME) === true) {
            $numbersOfProductsPerPage = $this->productsPerPage->getNumbersOfProductsPerPage();
            $selected = (int) $request->getCookieValue(ProductListingRequestHandler::PRODUCTS_PER_PAGE_COOKIE_NAME);
            return ProductsPerPage::create($numbersOfProductsPerPage, $selected);
        }

        return $this->productsPerPage;
    }

    /**
     * @param HttpRequest $request
     */
    private function processCookies(HttpRequest $request)
    {
        $productsPerPage = $this->getProductsPerPageQueryStringValue($request);
        if ($productsPerPage !== null) {
            setcookie(
                ProductListingRequestHandler::PRODUCTS_PER_PAGE_COOKIE_NAME,
                $productsPerPage,
                time() + ProductListingRequestHandler::PRODUCTS_PER_PAGE_COOKIE_TTL
            );
        }

        $sortOrder = $this->getSortOrderQueryStringValue($request);
        if ($sortOrder !== null) {
            setcookie(
                ProductListingRequestHandler::SORT_ORDER_COOKIE_NAME,
                $sortOrder,
                time() + ProductListingRequestHandler::SORT_ORDER_COOKIE_TTL
            );
        }

        $sortDirection = $this->getSortDirectionQueryStringValue($request);
        if ($sortDirection !== null) {
            setcookie(
                ProductListingRequestHandler::SORT_DIRECTION_COOKIE_NAME,
                $sortDirection,
                time() + ProductListingRequestHandler::SORT_DIRECTION_COOKIE_TTL
            );
        }
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    private function getProductsPerPageQueryStringValue(HttpRequest $request)
    {
        return $request->getQueryParameter(ProductListingRequestHandler::PRODUCTS_PER_PAGE_QUERY_PARAMETER_NAME);
    }

    /**
     * @param HttpRequest $request
     * @return SortOrderConfig
     */
    private function getSelectedSortOrderConfig(HttpRequest $request)
    {
        $sortOrderQueryStringValue = $this->getSortOrderQueryStringValue($request);
        $sortDirectionQueryStringValue = $this->getSortDirectionQueryStringValue($request);

        if ($sortOrderQueryStringValue !== null && $sortDirectionQueryStringValue !== null) {
            return $this->createSelectedSortOrderConfig($sortOrderQueryStringValue, $sortDirectionQueryStringValue);
        }

        if ($request->hasCookie(ProductListingRequestHandler::SORT_ORDER_COOKIE_NAME) === true &&
            $request->hasCookie(ProductListingRequestHandler::SORT_DIRECTION_COOKIE_NAME) === true
        ) {
            $sortOrder = $request->getCookieValue(ProductListingRequestHandler::SORT_ORDER_COOKIE_NAME);
            $direction = $request->getCookieValue(ProductListingRequestHandler::SORT_DIRECTION_COOKIE_NAME);
            return $this->createSelectedSortOrderConfig($sortOrder, $direction);
        }

        foreach ($this->sortOrderConfigs as $sortOrderConfig) {
            if ($sortOrderConfig->isSelected()) {
                return $sortOrderConfig;
            }
        }

        throw new NoSelectedSortOrderException('No selected sort order config is found.');
    }

    /**
     * @param string $attributeCodeString
     * @param string $direction
     * @return SortOrderConfig
     */
    private function createSelectedSortOrderConfig($attributeCodeString, $direction)
    {
        $attributeCode = AttributeCode::fromString($attributeCodeString);
        return SortOrderConfig::createSelected($attributeCode, $direction);
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    private function getSortOrderQueryStringValue(HttpRequest $request)
    {
        return $request->getQueryParameter(ProductListingRequestHandler::SORT_ORDER_QUERY_PARAMETER_NAME);
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    private function getSortDirectionQueryStringValue(HttpRequest $request)
    {
        return $request->getQueryParameter(ProductListingRequestHandler::SORT_DIRECTION_QUERY_PARAMETER_NAME);
    }
}
