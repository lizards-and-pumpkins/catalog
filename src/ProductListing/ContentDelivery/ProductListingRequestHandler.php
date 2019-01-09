<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\ContentDelivery;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\Website\UrlToWebsiteMap;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchResult;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchService;
use LizardsAndPumpkins\ProductSearch\QueryOptions;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\ProductListing\Import\ProductListingMetaSnippetContent;

class ProductListingRequestHandler implements HttpRequestHandler
{
    const CODE = 'product_listing';

    /**
     * @var Context
     */
    private $context;

    /**
     * @var FacetFiltersToIncludeInResult
     */
    private $facetFilterRequest;

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
     * @var ProductListingMetaSnippetContent
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
     * @var UrlToWebsiteMap
     */
    private $urlToWebsiteMap;

    /**
     * @param Context $context
     * @param FacetFiltersToIncludeInResult $facetFilterRequest
     * @param UrlToWebsiteMap $urlToWebsiteMap
     * @param ProductListingPageContentBuilder $productListingPageContentBuilder
     * @param ProductListingPageRequest $productListingPageRequest
     * @param ProductSearchService $productSearchService
     * @param mixed[] $pageMeta
     * @param SortBy $defaultSortBy
     * @param SortBy[] ...$availableSortBy
     */
    public function __construct(
        Context $context,
        FacetFiltersToIncludeInResult $facetFilterRequest,
        UrlToWebsiteMap $urlToWebsiteMap,
        ProductListingPageContentBuilder $productListingPageContentBuilder,
        ProductListingPageRequest $productListingPageRequest,
        ProductSearchService $productSearchService,
        array $pageMeta,
        SortBy $defaultSortBy,
        SortBy ...$availableSortBy
    ) {
        $this->context = $context;
        $this->facetFilterRequest = $facetFilterRequest;
        $this->urlToWebsiteMap = $urlToWebsiteMap;
        $this->productListingPageContentBuilder = $productListingPageContentBuilder;
        $this->productListingPageRequest = $productListingPageRequest;
        $this->productSearchService = $productSearchService;
        $this->pageMetaInfo = ProductListingMetaSnippetContent::fromArray($pageMeta);
        $this->defaultSortBy = $defaultSortBy;
        $this->availableSortBy = $availableSortBy;
    }

    public function canProcess(HttpRequest $request) : bool
    {
        return true;
    }

    public function process(HttpRequest $request) : HttpResponse
    {
        $this->productListingPageRequest->processCookies($request);

        $productsPerPage = $this->productListingPageRequest->getProductsPerPage($request);
        $selectedSortBy = $this->productListingPageRequest->getSelectedSortBy(
            $request,
            $this->defaultSortBy,
            ...$this->availableSortBy
        );
        $productSearchResult = $this->getSearchResults($request, $productsPerPage, $selectedSortBy);

        $requestUrlKey = $this->urlToWebsiteMap->getRequestPathWithoutWebsitePrefix((string) $request->getUrl());
        $keyGeneratorParams = [
            PageMetaInfoSnippetContent::URL_KEY => $requestUrlKey,
        ];

        return $this->productListingPageContentBuilder->buildPageContent(
            $this->pageMetaInfo,
            $this->context,
            $keyGeneratorParams,
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
    ) : ProductSearchResult {
        $currentPageNumber = $this->productListingPageRequest->getCurrentPageNumber($request);
        $numberOfProductsPerPage = $productsPerPage->getSelectedNumberOfProductsPerPage();

        $response = $this->getResults($request, $numberOfProductsPerPage, $currentPageNumber, $selectedSortBy);

        if ($this->isPageWithinBounds($response, $currentPageNumber, $numberOfProductsPerPage)) {
            return $response;
        }

        $lastPageNumber = $this->getLastPageNumber($response, $numberOfProductsPerPage);

        return $this->getResults($request, $numberOfProductsPerPage, $lastPageNumber, $selectedSortBy);
    }

    private function getResults(
        HttpRequest $request,
        int $numberOfProductsPerPage,
        int $currentPageNumber,
        SortBy $selectedSortBy
    ) : ProductSearchResult {
        $criteria = $this->pageMetaInfo->getSelectionCriteria();

        $queryOptions = QueryOptions::create(
            $this->productListingPageRequest->getSelectedFilterValues($request, $this->facetFilterRequest),
            $this->context,
            $this->facetFilterRequest,
            $numberOfProductsPerPage,
            $currentPageNumber,
            $this->productListingPageRequest->createSortByForRequest($selectedSortBy)
        );

        return $this->productSearchService->query($criteria, $queryOptions, '');
    }

    private function isPageWithinBounds(
        ProductSearchResult $productSearchResult,
        int $currentPageNumber,
        int $numberOfProductsPerPage
    ) : bool {
        return $currentPageNumber <= $this->getLastPageNumber($productSearchResult, $numberOfProductsPerPage);
    }

    private function getLastPageNumber(ProductSearchResult $productSearchResult, int $numberOfProductsPerPage) : int
    {
        return max(0, (int) ceil($productSearchResult->getTotalNumberOfResults() / $numberOfProductsPerPage) - 1);
    }
}
