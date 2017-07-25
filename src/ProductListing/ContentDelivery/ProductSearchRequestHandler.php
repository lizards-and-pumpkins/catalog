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
use LizardsAndPumpkins\Http\Routing\Exception\UnableToHandleRequestException;

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
     * @var ProductSearchResultMetaSnippetContent
     */
    private $pageMetaInfo;

    /**
     * @var SortBy
     */
    private $defaultSortBy;

    /**
     * @var SortBy[]
     */
    private $availableSortBy;

    /**
     * @param Context $context
     * @param FacetFiltersToIncludeInResult $facetFiltersToIncludeInResult
     * @param ProductListingPageContentBuilder $productListingPageContentBuilder
     * @param ProductListingPageRequest $productListingPageRequest
     * @param ProductSearchService $productSearchService
     * @param FullTextCriteriaBuilder $fullTextCriteriaBuilder
     * @param mixed[] $pageMeta
     * @param SortBy $defaultSortBy
     * @param SortBy[] ...$availableSortBy
     */
    public function __construct(
        Context $context,
        FacetFiltersToIncludeInResult $facetFiltersToIncludeInResult,
        ProductListingPageContentBuilder $productListingPageContentBuilder,
        ProductListingPageRequest $productListingPageRequest,
        ProductSearchService $productSearchService,
        FullTextCriteriaBuilder $fullTextCriteriaBuilder,
        array $pageMeta,
        SortBy $defaultSortBy,
        SortBy ...$availableSortBy
    ) {
        $this->context = $context;
        $this->facetFiltersToIncludeInResult = $facetFiltersToIncludeInResult;
        $this->productListingPageContentBuilder = $productListingPageContentBuilder;
        $this->productListingPageRequest = $productListingPageRequest;
        $this->productSearchService = $productSearchService;
        $this->fullTextCriteriaBuilder = $fullTextCriteriaBuilder;
        $this->pageMetaInfo = ProductSearchResultMetaSnippetContent::fromArray($pageMeta);
        $this->defaultSortBy = $defaultSortBy;
        $this->availableSortBy = $availableSortBy;
    }

    public function canProcess(HttpRequest $request) : bool
    {
        return $this->isValidSearchRequest($request);
    }

    public function process(HttpRequest $request) : HttpResponse
    {
        if (!$this->canProcess($request)) {
            throw new UnableToHandleRequestException(sprintf('Unable to process request with handler %s', __CLASS__));
        }

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

    private function isValidSearchRequest(HttpRequest $request) : bool
    {
        if (HttpRequest::METHOD_GET !== $request->getMethod()) {
            return false;
        }

        if (! $request->hasQueryParameter(self::QUERY_STRING_PARAMETER_NAME) ||
            strlen((string) $request->getQueryParameter(self::QUERY_STRING_PARAMETER_NAME)) < 1
        ) {
            return false;
        }

        return true;
    }

    private function getSearchResults(
        HttpRequest $request,
        ProductsPerPage $productsPerPage,
        SortBy $selectedSortBy
    ) : ProductSearchResult {
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
