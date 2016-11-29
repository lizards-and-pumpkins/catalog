<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\ContentDelivery;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderConfig;
use LizardsAndPumpkins\ProductSearch\QueryOptions;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
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
     * @var string[]
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

    public function __construct(
        Context $context,
        DataPoolReader $dataPoolReader,
        SnippetKeyGenerator $metaInfoSnippetKeyGenerator,
        FacetFiltersToIncludeInResult $facetFilterRequest,
        ProductListingPageContentBuilder $productListingPageContentBuilder,
        ProductListingPageRequest $productListingPageRequest
    ) {
        $this->dataPoolReader = $dataPoolReader;
        $this->context = $context;
        $this->metaInfoSnippetKeyGenerator = $metaInfoSnippetKeyGenerator;
        $this->facetFilterRequest = $facetFilterRequest;
        $this->productListingPageContentBuilder = $productListingPageContentBuilder;
        $this->productListingPageRequest = $productListingPageRequest;
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
        $selectedSortOrderConfig = $this->productListingPageRequest->getSelectedSortOrderConfig($request);
        $searchEngineResponse = $this->getSearchResultsMatchingCriteria(
            $request,
            $productsPerPage,
            $selectedSortOrderConfig
        );

        $metaInfoSnippetContent = $this->getPageMetaInfo();
        $keyGeneratorParams = [
            'robots' => 'noindex',
        ];

        return $this->productListingPageContentBuilder->buildPageContent(
            $metaInfoSnippetContent,
            $this->context,
            $keyGeneratorParams,
            $searchEngineResponse,
            $productsPerPage,
            $selectedSortOrderConfig
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

        if (strlen((string) $request->getQueryParameter(self::QUERY_STRING_PARAMETER_NAME)) < 1) {
            return false;
        }

        return true;
    }

    private function getSearchResultsMatchingCriteria(
        HttpRequest $request,
        ProductsPerPage $productsPerPage,
        SortOrderConfig $selectedSortOrderConfig
    ) : SearchEngineResponse {
        $requestSortOrder = $this->productListingPageRequest->createSortOrderConfigForRequest($selectedSortOrderConfig);

        $queryOptions = QueryOptions::create(
            $this->productListingPageRequest->getSelectedFilterValues($request, $this->facetFilterRequest),
            $this->context,
            $this->facetFilterRequest,
            $productsPerPage->getSelectedNumberOfProductsPerPage(),
            $this->productListingPageRequest->getCurrentPageNumber($request),
            $requestSortOrder
        );

        $queryString = $request->getQueryParameter(self::QUERY_STRING_PARAMETER_NAME);

        return $this->dataPoolReader->getSearchResultsMatchingString($queryString, $queryOptions);
    }

    private function getPageMetaInfo() : ProductSearchResultMetaSnippetContent
    {
        $metaInfoSnippetKey = $this->metaInfoSnippetKeyGenerator->getKeyForContext($this->context, []);
        $metaInfoSnippetJson = $this->dataPoolReader->getSnippet($metaInfoSnippetKey);
        $metaInfoSnippetContent = ProductSearchResultMetaSnippetContent::fromJson($metaInfoSnippetJson);

        return $metaInfoSnippetContent;
    }
}
