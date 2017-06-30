<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\ContentDelivery;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\Website\UrlToWebsiteMap;
use LizardsAndPumpkins\DataPool\DataPoolReader;
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
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;

class ProductSearchRequestHandler implements HttpRequestHandler
{
    const CODE = 'product_search';
    const SEARCH_RESULTS_SLUG = 'catalogsearch/result';
    const QUERY_STRING_PARAMETER_NAME = 'q';

    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var SnippetKeyGenerator
     */
    private $metaInfoSnippetKeyGenerator;

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

    /**
     * @var UrlToWebsiteMap
     */
    private $urlToWebsiteMap;

    public function __construct(
        Context $context,
        DataPoolReader $dataPoolReader,
        SnippetKeyGenerator $metaInfoSnippetKeyGenerator,
        FacetFiltersToIncludeInResult $facetFiltersToIncludeInResult,
        UrlToWebsiteMap $urlToWebsiteMap,
        ProductListingPageContentBuilder $productListingPageContentBuilder,
        ProductListingPageRequest $productListingPageRequest,
        ProductSearchService $productSearchService,
        FullTextCriteriaBuilder $fullTextCriteriaBuilder,
        SortBy $defaultSortBy,
        SortBy ...$availableSortBy
    ) {
        $this->dataPoolReader = $dataPoolReader;
        $this->context = $context;
        $this->metaInfoSnippetKeyGenerator = $metaInfoSnippetKeyGenerator;
        $this->facetFiltersToIncludeInResult = $facetFiltersToIncludeInResult;
        $this->productListingPageContentBuilder = $productListingPageContentBuilder;
        $this->productListingPageRequest = $productListingPageRequest;
        $this->productSearchService = $productSearchService;
        $this->fullTextCriteriaBuilder = $fullTextCriteriaBuilder;
        $this->defaultSortBy = $defaultSortBy;
        $this->availableSortBy = $availableSortBy;
        $this->urlToWebsiteMap = $urlToWebsiteMap;
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

        $metaInfoSnippetContent = $this->getPageMetaInfo();

        return $this->productListingPageContentBuilder->buildPageContent(
            $metaInfoSnippetContent,
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
        $pathWithoutWebsitePrefix = $this->urlToWebsiteMap->getRequestPathWithoutWebsitePrefix((string) $request->getUrl());
        $urlPathWithoutTrailingSlash = rtrim($pathWithoutWebsitePrefix, '/');

        if (self::SEARCH_RESULTS_SLUG !== $urlPathWithoutTrailingSlash) {
            return false;
        }

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

    private function getPageMetaInfo() : ProductSearchResultMetaSnippetContent
    {
        $metaInfoSnippetKey = $this->metaInfoSnippetKeyGenerator->getKeyForContext($this->context, []);
        $metaInfoSnippetJson = $this->dataPoolReader->getSnippet($metaInfoSnippetKey);
        $metaInfoSnippetContent = ProductSearchResultMetaSnippetContent::fromJson($metaInfoSnippetJson);

        return $metaInfoSnippetContent;
    }
}
