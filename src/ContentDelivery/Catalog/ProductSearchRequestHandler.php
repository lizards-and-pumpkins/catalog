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
use LizardsAndPumpkins\PageBuilder;
use LizardsAndPumpkins\Product\ProductSearchResultMetaSnippetContent;
use LizardsAndPumpkins\Product\ProductSearchResultMetaSnippetRenderer;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator;

class ProductSearchRequestHandler implements HttpRequestHandler
{
    use ProductListingRequestHandlerTrait;

    const SEARCH_RESULTS_SLUG = 'catalogsearch/result';
    const QUERY_STRING_PARAMETER_NAME = 'q';
    const SEARCH_QUERY_MINIMUM_LENGTH = 3;

    /**
     * @var string[]
     */
    private $searchableAttributeCodes;

    /**
     * @param Context $context
     * @param DataPoolReader $dataPoolReader
     * @param PageBuilder $pageBuilder
     * @param SnippetKeyGeneratorLocator $keyGeneratorLocator
     * @param string[] $filterNavigationConfig
     * @param ProductsPerPage $productsPerPage
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param string[] $searchableAttributeCodes
     * @param SortOrderConfig[] $sortOrderConfigs
     */
    public function __construct(
        Context $context,
        DataPoolReader $dataPoolReader,
        PageBuilder $pageBuilder,
        SnippetKeyGeneratorLocator $keyGeneratorLocator,
        array $filterNavigationConfig,
        ProductsPerPage $productsPerPage,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $searchableAttributeCodes,
        SortOrderConfig ...$sortOrderConfigs
    ) {
        $this->dataPoolReader = $dataPoolReader;
        $this->context = $context;
        $this->pageBuilder = $pageBuilder;
        $this->keyGeneratorLocator = $keyGeneratorLocator;
        $this->filterNavigationConfig = $filterNavigationConfig;
        $this->productsPerPage = $productsPerPage;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->searchableAttributeCodes = $searchableAttributeCodes;
        $this->sortOrderConfigs = $sortOrderConfigs;
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

        $this->processCookies($request);

        $productsPerPage = $this->getProductsPerPage($request);
        $searchEngineResponse = $this->getSearchResultsMatchingCriteria($request);
        $this->addProductListingContentToPage($searchEngineResponse, $productsPerPage);

        $metaInfoSnippetKeyGenerator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode(
            ProductSearchResultMetaSnippetRenderer::CODE
        );
        $metaInfoSnippetKey = $metaInfoSnippetKeyGenerator->getKeyForContext($this->context, []);
        $metaInfoSnippetJson = $this->dataPoolReader->getSnippet($metaInfoSnippetKey);
        $metaInfoSnippetContent = ProductSearchResultMetaSnippetContent::fromJson($metaInfoSnippetJson);

        $keyGeneratorParams = [];

        return $this->pageBuilder->buildPage($metaInfoSnippetContent, $this->context, $keyGeneratorParams);
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
     * @return SearchEngineResponse
     */
    private function getSearchResultsMatchingCriteria(HttpRequest $request)
    {
        $selectedFilters = $this->getSelectedFilterValuesFromRequest($request);

        $queryString = $request->getQueryParameter(self::QUERY_STRING_PARAMETER_NAME);
        $criteria = $this->searchCriteriaBuilder->anyOfFieldsContainString(
            $this->searchableAttributeCodes,
            $queryString
        );
        $productsPerPage = $this->getProductsPerPage($request);
        $currentPageNumber = $this->getCurrentPageNumber($request);
        $selectedSortOrderConfig = $this->getSelectedSortOrderConfig($request);

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
}
