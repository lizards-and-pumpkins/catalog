<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\KeyValue\Exception\KeyNotFoundException;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\QueryOptions;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\Http\Exception\UnableToHandleRequestException;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestHandler;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Product\ProductListingCriteriaSnippetContent;
use LizardsAndPumpkins\SnippetKeyGenerator;

class ProductListingRequestHandler implements HttpRequestHandler
{
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
        $this->context = $context;
        $this->dataPoolReader = $dataPoolReader;
        $this->metaInfoSnippetKeyGenerator = $metaInfoSnippetKeyGenerator;
        $this->facetFilterRequest = $facetFilterRequest;
        $this->productListingPageContentBuilder = $productListingPageContentBuilder;
        $this->productListingPageRequest = $productListingPageRequest;
    }

    /**
     * @var ProductListingCriteriaSnippetContent|bool
     */
    private $memoizedPageMetaInfo;

    /**
     * @param HttpRequest $request
     * @return bool
     */
    public function canProcess(HttpRequest $request)
    {
        return $this->getPageMetaInfoSnippet($request) !== false;
    }

    /**
     * @param HttpRequest $request
     * @return HttpResponse
     */
    public function process(HttpRequest $request)
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

        $metaInfo = $this->getPageMetaInfoSnippet($request);
        $keyGeneratorParams = [
            PageMetaInfoSnippetContent::URL_KEY => ltrim($request->getUrlPathRelativeToWebFront(), '/')
        ];

        return $this->productListingPageContentBuilder->buildPageContent(
            $metaInfo,
            $this->context,
            $keyGeneratorParams,
            $searchEngineResponse,
            $productsPerPage,
            $selectedSortOrderConfig
        );
    }

    /**
     * @param HttpRequest $request
     * @return bool|ProductListingCriteriaSnippetContent
     */
    private function getPageMetaInfoSnippet(HttpRequest $request)
    {
        if (null === $this->memoizedPageMetaInfo) {
            $this->memoizedPageMetaInfo = false;
            $metaInfoSnippetKey = $this->getMetaInfoSnippetKey($request);
            $json = $this->getPageMetaInfoJsonIfExists($metaInfoSnippetKey);
            if ($json) {
                $this->memoizedPageMetaInfo = ProductListingCriteriaSnippetContent::fromJson($json);
            }
        }

        return $this->memoizedPageMetaInfo;
    }

    /**
     * @param string $metaInfoSnippetKey
     * @return string
     */
    private function getPageMetaInfoJsonIfExists($metaInfoSnippetKey)
    {
        try {
            $snippet = $this->dataPoolReader->getSnippet($metaInfoSnippetKey);
        } catch (KeyNotFoundException $e) {
            $snippet = '';
        }
        return $snippet;
    }

    /**
     * @param HttpRequest $request
     * @param ProductsPerPage $productsPerPage
     * @param SortOrderConfig $selectedSortOrderConfig
     * @return SearchEngineResponse
     */
    private function getSearchResultsMatchingCriteria(
        HttpRequest $request,
        ProductsPerPage $productsPerPage,
        SortOrderConfig $selectedSortOrderConfig
    ) {
        $currentPageNumber = $this->productListingPageRequest->getCurrentPageNumber($request);
        $numberOfProductsPerPage = $productsPerPage->getSelectedNumberOfProductsPerPage();

        $response = $this->getResults($request, $numberOfProductsPerPage, $currentPageNumber, $selectedSortOrderConfig);

        if ($this->isPageWithinBounds($response, $currentPageNumber, $numberOfProductsPerPage)) {
            return $response;
        }

        $lastPageNumber = $this->getLastPageNumber($response, $numberOfProductsPerPage);

        return $this->getResults($request, $numberOfProductsPerPage, $lastPageNumber, $selectedSortOrderConfig);
    }

    private function getResults(
        HttpRequest $request,
        $numberOfProductsPerPage,
        $currentPageNumber,
        SortOrderConfig $selectedSortOrderConfig
    ) {
        $criteria = $this->getPageMetaInfoSnippet($request)->getSelectionCriteria();
        $selectedFilters = $this->productListingPageRequest->getSelectedFilterValues(
            $request,
            $this->facetFilterRequest
        );
        $requestSortOrder = $this->productListingPageRequest->createSortOrderConfigForRequest($selectedSortOrderConfig);

        $queryOptions = QueryOptions::create(
            $selectedFilters,
            $this->context,
            $this->facetFilterRequest,
            $numberOfProductsPerPage,
            $currentPageNumber,
            $requestSortOrder
        );

        return $this->dataPoolReader->getSearchResultsMatchingCriteria($criteria, $queryOptions);
    }

    /**
     * @param SearchEngineResponse $searchEngineResponse
     * @param int $currentPageNumber
     * @param int $numberOfProductsPerPage
     * @return bool
     */
    private function isPageWithinBounds(
        SearchEngineResponse $searchEngineResponse,
        $currentPageNumber,
        $numberOfProductsPerPage
    ) {
        return $currentPageNumber <= $this->getLastPageNumber($searchEngineResponse, $numberOfProductsPerPage);
    }

    /**
     * @param SearchEngineResponse $searchEngineResponse
     * @param int $numberOfProductsPerPage
     * @return int
     */
    private function getLastPageNumber(SearchEngineResponse $searchEngineResponse, $numberOfProductsPerPage)
    {
        return max(0, (int) ceil($searchEngineResponse->getTotalNumberOfResults() / $numberOfProductsPerPage) - 1);
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    private function getMetaInfoSnippetKey(HttpRequest $request)
    {
        $urlKey = $request->getUrlPathRelativeToWebFront();
        $metaInfoSnippetKey = $this->metaInfoSnippetKeyGenerator->getKeyForContext(
            $this->context,
            [PageMetaInfoSnippetContent::URL_KEY => $urlKey]
        );

        return $metaInfoSnippetKey;
    }
}
