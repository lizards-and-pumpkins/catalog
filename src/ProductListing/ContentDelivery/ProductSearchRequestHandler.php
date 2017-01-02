<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\ContentDelivery;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionFullText;
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
     * @var SortBy
     */
    private $defaultSortBy;

    /**
     * @var SortBy[]
     */
    private $availableSortBy;

    public function __construct(
        Context $context,
        DataPoolReader $dataPoolReader,
        SnippetKeyGenerator $metaInfoSnippetKeyGenerator,
        FacetFiltersToIncludeInResult $facetFiltersToIncludeInResult,
        ProductListingPageContentBuilder $productListingPageContentBuilder,
        ProductListingPageRequest $productListingPageRequest,
        ProductSearchService $productSearchService,
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
        $productSearchResult = $this->getSearchResultsMatchingCriteria($request, $productsPerPage, $selectedSortBy);

        $metaInfoSnippetContent = $this->getPageMetaInfo();
        $keyGeneratorParams = [
            'robots' => 'noindex',
        ];

        return $this->productListingPageContentBuilder->buildPageContent(
            $metaInfoSnippetContent,
            $this->context,
            $keyGeneratorParams,
            $productSearchResult,
            $productsPerPage,
            $selectedSortBy,
            ...$this->availableSortBy
        );
    }

    private function isValidSearchRequest(HttpRequest $request) : bool
    {
        $urlPathWithoutTrailingSlash = rtrim($request->getPathWithoutWebsitePrefix(), '/');

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

    private function getSearchResultsMatchingCriteria(
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
        $criteria = new SearchCriterionFullText($queryString);

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
