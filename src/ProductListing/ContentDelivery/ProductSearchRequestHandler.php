<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\ContentDelivery;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\FullTextCriteriaBuilder;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchResult;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchService;
use LizardsAndPumpkins\ProductSearch\QueryOptions;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use LizardsAndPumpkins\Http\HttpResponse;

class ProductSearchRequestHandler implements HttpRequestHandler
{
    const CODE = 'product_search';
    const SEARCH_RESULTS_SLUG = 'catalogsearch/result';
    const QUERY_STRING_PARAMETER_NAME = 'q';

    /**
     * @var Context
     */
    private $context;

    /**
     * @var ProductSearchResultMetaSnippetContent
     */
    private $pageMetaInfo;

    /**
     * @var FacetFiltersToIncludeInResult
     */
    private $facetFiltersToIncludeInResult;

    /**
     * @var ProductListingPageContentBuilder
     */
    private $productListingPageContentBuilder;

    /**
     * @var ProductListingPageRequest
     */
    private $productListingPageRequest;

    /**
     * @var ProductSearchService
     */
    private $productSearchService;

    /**
     * @var FullTextCriteriaBuilder
     */
    private $fullTextCriteriaBuilder;

    /**
     * @var SortBy
     */
    private $defaultSortBy;

    /**
     * @var SortBy[]
     */
    private $availableSortBy;

    public function __construct(
        Context $context,
        string $metaInfoJson,
        FacetFiltersToIncludeInResult $facetFiltersToIncludeInResult,
        ProductListingPageContentBuilder $productListingPageContentBuilder,
        ProductListingPageRequest $productListingPageRequest,
        ProductSearchService $productSearchService,
        FullTextCriteriaBuilder $fullTextCriteriaBuilder,
        SortBy $defaultSortBy,
        SortBy ...$availableSortBy
    ) {
        $this->context = $context;
        $this->pageMetaInfo = ProductSearchResultMetaSnippetContent::fromJson($metaInfoJson);
        $this->facetFiltersToIncludeInResult = $facetFiltersToIncludeInResult;
        $this->productListingPageContentBuilder = $productListingPageContentBuilder;
        $this->productListingPageRequest = $productListingPageRequest;
        $this->productSearchService = $productSearchService;
        $this->fullTextCriteriaBuilder = $fullTextCriteriaBuilder;
        $this->defaultSortBy = $defaultSortBy;
        $this->availableSortBy = $availableSortBy;
    }

    public function process(HttpRequest $request): HttpResponse
    {
        $this->productListingPageRequest->processCookies($request);

        $productsPerPage = $this->productListingPageRequest->getProductsPerPage($request);
        $selectedSortBy = $this->productListingPageRequest->getSelectedSortBy(
            $request,
            $this->defaultSortBy,
            ...$this->availableSortBy
        );
        $productSearchResult = $this->getSearchResults($request, $productsPerPage, $selectedSortBy);

        return $this->productListingPageContentBuilder->buildPageContent(
            $this->pageMetaInfo,
            $this->context,
            $keyGeneratorParams = [],
            $productSearchResult,
            $productsPerPage,
            $selectedSortBy,
            ...$this->availableSortBy
        );
    }

    private function getSearchResults(
        HttpRequest $request,
        ProductsPerPage $productsPerPage,
        SortBy $selectedSortBy
    ): ProductSearchResult {
        $queryOptions = QueryOptions::create(
            $this->productListingPageRequest->getSelectedFilterValues($request, $this->facetFiltersToIncludeInResult),
            $this->context,
            $this->facetFiltersToIncludeInResult,
            $productsPerPage->getSelectedNumberOfProductsPerPage(),
            $this->productListingPageRequest->getCurrentPageNumber($request),
            $this->productListingPageRequest->createSortByForRequest($selectedSortBy)
        );

        $queryString = $request->getQueryParameter(self::QUERY_STRING_PARAMETER_NAME);
        $criteria = $this->fullTextCriteriaBuilder->createFromString($queryString);

        return $this->productSearchService->query($criteria, $queryOptions);
    }
}
