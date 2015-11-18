<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestHandler;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\Exception\UnableToHandleRequestException;
use LizardsAndPumpkins\Product\ProductSearchResultMetaSnippetContent;
use LizardsAndPumpkins\SnippetKeyGenerator;

class ProductSearchRequestHandler implements HttpRequestHandler
{
    const SEARCH_RESULTS_SLUG = 'catalogsearch/result';
    const QUERY_STRING_PARAMETER_NAME = 'q';
    const SEARCH_QUERY_MINIMUM_LENGTH = 3;

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
    private $filterNavigationConfig;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var string[]
     */
    private $searchableAttributeCodes;

    /**
     * @var ProductListingPageContentBuilder
     */
    private $productListingPageContentBuilder;

    /**
     * @var ProductListingPageRequest
     */
    private $productListingPageRequest;

    /**
     * @param Context $context
     * @param DataPoolReader $dataPoolReader
     * @param SnippetKeyGenerator $metaInfoSnippetKeyGenerator
     * @param string[] $filterNavigationConfig
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param string[] $searchableAttributeCodes
     * @param ProductListingPageContentBuilder $productListingPageContentBuilder
     * @param ProductListingPageRequest $productListingPageRequest
     */
    public function __construct(
        Context $context,
        DataPoolReader $dataPoolReader,
        SnippetKeyGenerator $metaInfoSnippetKeyGenerator,
        array $filterNavigationConfig,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $searchableAttributeCodes,
        ProductListingPageContentBuilder $productListingPageContentBuilder,
        ProductListingPageRequest $productListingPageRequest
    ) {
        $this->dataPoolReader = $dataPoolReader;
        $this->context = $context;
        $this->metaInfoSnippetKeyGenerator = $metaInfoSnippetKeyGenerator;
        $this->filterNavigationConfig = $filterNavigationConfig;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchableAttributeCodes = $searchableAttributeCodes;
        $this->productListingPageContentBuilder = $productListingPageContentBuilder;
        $this->productListingPageRequest = $productListingPageRequest;
    }

    /**
     * @param HttpRequest $request
     * @return bool
     */
    public function canProcess(HttpRequest $request)
    {
        return $this->isValidSearchRequest($request);
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

        $metaInfoSnippetContent = $this->getPageMetaInfo();
        $keyGeneratorParams = [];

        return $this->productListingPageContentBuilder->buildPageContent(
            $metaInfoSnippetContent,
            $this->context,
            $keyGeneratorParams,
            $searchEngineResponse,
            $productsPerPage,
            $selectedSortOrderConfig
        );
    }

    /**
     * @param HttpRequest $request
     * @return bool
     */
    private function isValidSearchRequest(HttpRequest $request)
    {
        $urlPathWithoutTrailingSlash = rtrim($request->getUrlPathRelativeToWebFront(), '/');

        if (self::SEARCH_RESULTS_SLUG !== $urlPathWithoutTrailingSlash) {
            return false;
        }

        if (HttpRequest::METHOD_GET !== $request->getMethod()) {
            return false;
        }

        $searchQueryString = $request->getQueryParameter(self::QUERY_STRING_PARAMETER_NAME);

        if (null === $searchQueryString || self::SEARCH_QUERY_MINIMUM_LENGTH > strlen($searchQueryString)) {
            return false;
        }

        return true;
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
        $selectedFilters = $this->productListingPageRequest->getSelectedFilterValues(
            $request,
            $this->filterNavigationConfig
        );

        $queryString = $request->getQueryParameter(self::QUERY_STRING_PARAMETER_NAME);
        $criteria = $this->searchCriteriaBuilder->createCriteriaForAnyOfGivenFieldsContainsString(
            $this->searchableAttributeCodes,
            $queryString
        );
        $currentPageNumber = $this->productListingPageRequest->getCurrentPageNumber($request);

        return $this->dataPoolReader->getSearchResultsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $this->context,
            $this->filterNavigationConfig,
            $productsPerPage->getSelectedNumberOfProductsPerPage(),
            $currentPageNumber,
            $selectedSortOrderConfig
        );
    }

    /**
     * @return ProductSearchResultMetaSnippetContent
     */
    private function getPageMetaInfo()
    {
        $metaInfoSnippetKey = $this->metaInfoSnippetKeyGenerator->getKeyForContext($this->context, []);
        $metaInfoSnippetJson = $this->dataPoolReader->getSnippet($metaInfoSnippetKey);
        $metaInfoSnippetContent = ProductSearchResultMetaSnippetContent::fromJson($metaInfoSnippetJson);

        return $metaInfoSnippetContent;
    }
}
