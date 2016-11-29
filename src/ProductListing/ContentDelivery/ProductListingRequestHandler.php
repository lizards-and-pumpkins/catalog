<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\ContentDelivery;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\KeyValueStore\Exception\KeyNotFoundException;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\ProductSearch\QueryOptions;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\Http\Routing\Exception\UnableToHandleRequestException;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\ProductListing\Import\ProductListingSnippetContent;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;

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

    /**
     * @var SelectProductListingRobotsMetaTagContent
     */
    private $selectProductListingRobotsMetaTagContent;

    public function __construct(
        Context $context,
        DataPoolReader $dataPoolReader,
        SnippetKeyGenerator $metaInfoSnippetKeyGenerator,
        FacetFiltersToIncludeInResult $facetFilterRequest,
        ProductListingPageContentBuilder $productListingPageContentBuilder,
        SelectProductListingRobotsMetaTagContent $selectProductListingRobotsMetaTagContent,
        ProductListingPageRequest $productListingPageRequest
    ) {
        $this->context = $context;
        $this->dataPoolReader = $dataPoolReader;
        $this->metaInfoSnippetKeyGenerator = $metaInfoSnippetKeyGenerator;
        $this->facetFilterRequest = $facetFilterRequest;
        $this->productListingPageContentBuilder = $productListingPageContentBuilder;
        $this->selectProductListingRobotsMetaTagContent = $selectProductListingRobotsMetaTagContent;
        $this->productListingPageRequest = $productListingPageRequest;
    }

    /**
     * @var ProductListingSnippetContent|bool
     */
    private $memoizedPageMetaInfo;

    public function canProcess(HttpRequest $request) : bool
    {
        return $this->getPageMetaInfoSnippet($request) !== false;
    }

    public function process(HttpRequest $request) : HttpResponse
    {
        if (!$this->canProcess($request)) {
            throw new UnableToHandleRequestException(sprintf('Unable to process request with handler %s', __CLASS__));
        }

        $this->productListingPageRequest->processCookies($request);

        $productsPerPage = $this->productListingPageRequest->getProductsPerPage($request);
        $selectedSortBy = $this->productListingPageRequest->getSelectedSortBy($request);
        $searchEngineResponse = $this->getSearchResultsMatchingCriteria($request, $productsPerPage, $selectedSortBy);
        
        $metaInfo = $this->getPageMetaInfoSnippet($request);
        $keyGeneratorParams = [
            PageMetaInfoSnippetContent::URL_KEY => ltrim($request->getPathWithoutWebsitePrefix(), '/'),
            'robots' => $this->selectProductListingRobotsMetaTagContent->getRobotsMetaTagContentForRequest($request),
        ];

        return $this->productListingPageContentBuilder->buildPageContent(
            $metaInfo,
            $this->context,
            $keyGeneratorParams,
            $searchEngineResponse,
            $productsPerPage,
            $selectedSortBy
        );
    }

    /**
     * @param HttpRequest $request
     * @return bool|ProductListingSnippetContent
     */
    private function getPageMetaInfoSnippet(HttpRequest $request)
    {
        if (null === $this->memoizedPageMetaInfo) {
            $this->memoizedPageMetaInfo = false;
            $metaInfoSnippetKey = $this->getMetaInfoSnippetKey($request);
            $json = $this->getPageMetaInfoJsonIfExists($metaInfoSnippetKey);
            if ($json) {
                $this->memoizedPageMetaInfo = ProductListingSnippetContent::fromJson($json);
            }
        }

        return $this->memoizedPageMetaInfo;
    }

    /**
     * @param string $metaInfoSnippetKey
     * @return mixed
     */
    private function getPageMetaInfoJsonIfExists(string $metaInfoSnippetKey)
    {
        try {
            $snippet = $this->dataPoolReader->getSnippet($metaInfoSnippetKey);
        } catch (KeyNotFoundException $e) {
            $snippet = '';
        }
        return $snippet;
    }

    private function getSearchResultsMatchingCriteria(
        HttpRequest $request,
        ProductsPerPage $productsPerPage,
        SortBy $selectedSortBy
    ) : SearchEngineResponse {
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
    ) : SearchEngineResponse {
        $criteria = $this->getPageMetaInfoSnippet($request)->getSelectionCriteria();
        $selectedFilters = $this->productListingPageRequest->getSelectedFilterValues(
            $request,
            $this->facetFilterRequest
        );
        $requestSortOrder = $this->productListingPageRequest->createSortByForRequest($selectedSortBy);

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

    private function isPageWithinBounds(
        SearchEngineResponse $searchEngineResponse,
        int $currentPageNumber,
        int $numberOfProductsPerPage
    ) : bool {
        return $currentPageNumber <= $this->getLastPageNumber($searchEngineResponse, $numberOfProductsPerPage);
    }

    private function getLastPageNumber(SearchEngineResponse $searchEngineResponse, int $numberOfProductsPerPage) : int
    {
        return max(0, (int) ceil($searchEngineResponse->getTotalNumberOfResults() / $numberOfProductsPerPage) - 1);
    }

    private function getMetaInfoSnippetKey(HttpRequest $request) : string
    {
        $urlKey = $request->getPathWithoutWebsitePrefix();
        $metaInfoSnippetKey = $this->metaInfoSnippetKeyGenerator->getKeyForContext(
            $this->context,
            [PageMetaInfoSnippetContent::URL_KEY => $urlKey]
        );

        return $metaInfoSnippetKey;
    }
}
