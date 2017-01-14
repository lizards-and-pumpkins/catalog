<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\ContentDelivery;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\KeyValueStore\Exception\KeyNotFoundException;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchResult;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchService;
use LizardsAndPumpkins\ProductSearch\QueryOptions;
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
        FacetFiltersToIncludeInResult $facetFilterRequest,
        ProductListingPageContentBuilder $productListingPageContentBuilder,
        SelectProductListingRobotsMetaTagContent $selectProductListingRobotsMetaTagContent,
        ProductListingPageRequest $productListingPageRequest,
        ProductSearchService $productSearchService,
        SortBy $defaultSortBy,
        SortBy ...$availableSortBy
    ) {
        $this->context = $context;
        $this->dataPoolReader = $dataPoolReader;
        $this->metaInfoSnippetKeyGenerator = $metaInfoSnippetKeyGenerator;
        $this->facetFilterRequest = $facetFilterRequest;
        $this->productListingPageContentBuilder = $productListingPageContentBuilder;
        $this->selectProductListingRobotsMetaTagContent = $selectProductListingRobotsMetaTagContent;
        $this->productListingPageRequest = $productListingPageRequest;
        $this->productSearchService = $productSearchService;
        $this->defaultSortBy = $defaultSortBy;
        $this->availableSortBy = $availableSortBy;
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
        $selectedSortBy = $this->productListingPageRequest->getSelectedSortBy(
            $request,
            $this->defaultSortBy,
            ...$this->availableSortBy
        );
        $productSearchResult = $this->getSearchResults($request, $productsPerPage, $selectedSortBy);
        
        $metaInfo = $this->getPageMetaInfoSnippet($request);
        $keyGeneratorParams = [
            PageMetaInfoSnippetContent::URL_KEY => ltrim($request->getPathWithoutWebsitePrefix(), '/'),
            'robots' => $this->selectProductListingRobotsMetaTagContent->getRobotsMetaTagContentForRequest($request),
        ];

        return $this->productListingPageContentBuilder->buildPageContent(
            $metaInfo,
            $this->context,
            $keyGeneratorParams,
            $productSearchResult,
            $productsPerPage,
            $selectedSortBy,
            ...$this->availableSortBy
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
        $criteria = $this->getPageMetaInfoSnippet($request)->getSelectionCriteria();

        $queryOptions = QueryOptions::create(
            $this->productListingPageRequest->getSelectedFilterValues($request, $this->facetFilterRequest),
            $this->context,
            $this->facetFilterRequest,
            $numberOfProductsPerPage,
            $currentPageNumber,
            $this->productListingPageRequest->createSortByForRequest($selectedSortBy)
        );

        return $this->productSearchService->query($criteria, $queryOptions);
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
