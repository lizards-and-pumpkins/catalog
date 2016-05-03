<?php

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderConfig;
use LizardsAndPumpkins\ProductSearch\QueryOptions;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\Routing\UnableToHandleRequestException;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageBuilder;
use LizardsAndPumpkins\Import\Product\Product;
use LizardsAndPumpkins\Import\Product\ProductId;
use LizardsAndPumpkins\ProductSearch\ProductInSearchAutosuggestionSnippetRenderer;
use LizardsAndPumpkins\ProductSearch\Import\ProductSearchAutosuggestionMetaSnippetContent;
use LizardsAndPumpkins\ProductSearch\Import\ProductSearchAutosuggestionMetaSnippetRenderer;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGeneratorLocator;

class ProductSearchAutosuggestionRequestHandler implements HttpRequestHandler
{
    const SEARCH_RESULTS_SLUG = 'catalogsearch/suggest';
    const QUERY_STRING_PARAMETER_NAME = 'q';

    /**
     * @var Context
     */
    private $context;

    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    /**
     * @var PageBuilder
     */
    private $pageBuilder;

    /**
     * @var SnippetKeyGeneratorLocator
     */
    private $keyGeneratorLocator;

    /**
     * @var SortOrderConfig
     */
    private $sortOrderConfig;

    public function __construct(
        Context $context,
        DataPoolReader $dataPoolReader,
        PageBuilder $pageBuilder,
        SnippetKeyGeneratorLocator $keyGeneratorLocator,
        SortOrderConfig $sortOrderConfig
    ) {
        $this->context = $context;
        $this->dataPoolReader = $dataPoolReader;
        $this->pageBuilder = $pageBuilder;
        $this->keyGeneratorLocator = $keyGeneratorLocator;
        $this->sortOrderConfig = $sortOrderConfig;
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
        if (!$this->isValidSearchRequest($request)) {
            throw new UnableToHandleRequestException(sprintf('Unable to process request with handler %s', __CLASS__));
        }

        $searchQueryString = $request->getQueryParameter(self::QUERY_STRING_PARAMETER_NAME);
        $response = $this->getSearchEngineResponse($searchQueryString);
        $this->addSearchResultsToPageBuilder(...$response->getProductIds());

        $metaInfoSnippetContent = $this->getMetaInfoSnippetContent();

        $this->addTotalNumberOfResultsSnippetToPageBuilder($response->getTotalNumberOfResults());
        $this->addSearchQueryStringSnippetToPageBuilder($searchQueryString);

        $keyGeneratorParams = [];

        return $this->pageBuilder->buildPage($metaInfoSnippetContent, $this->context, $keyGeneratorParams);
    }

    /**
     * @param HttpRequest $request
     * @return bool
     */
    private function isValidSearchRequest(HttpRequest $request)
    {
        $urlPathWithoutTrailingSlash = rtrim($request->getPathWithoutWebsitePrefix(), '/');

        if (self::SEARCH_RESULTS_SLUG !== $urlPathWithoutTrailingSlash) {
            return false;
        }

        if (HttpRequest::METHOD_GET !== $request->getMethod()) {
            return false;
        }

        if (strlen($request->getQueryParameter(self::QUERY_STRING_PARAMETER_NAME)) < 1) {
            return false;
        }

        return true;
    }

    /**
     * @param string $queryString
     * @return SearchEngineResponse
     */
    private function getSearchEngineResponse($queryString)
    {
        $selectedFilters = [];
        $facetFilterRequest = new FacetFiltersToIncludeInResult;
        $rowsPerPage = 5; // TODO: Replace with configured number of suggestions to show
        $pageNumber = 0;

        $queryOptions = QueryOptions::create(
            $selectedFilters,
            $this->context,
            $facetFilterRequest,
            $rowsPerPage,
            $pageNumber,
            $this->sortOrderConfig
        );

        return $this->dataPoolReader->getSearchResultsMatchingString($queryString, $queryOptions);
    }

    private function addSearchResultsToPageBuilder(ProductId ...$productIds)
    {
        if (0 === count($productIds)) {
            return;
        }

        $productInAutosuggestionSnippetKeys = $this->getProductInAutosuggestionSnippetKeys(...$productIds);
        $snippetKeyToContentMap = $this->dataPoolReader->getSnippets($productInAutosuggestionSnippetKeys);
        $snippetCodeToKeyMap = $this->getProductInAutosuggestionSnippetCodeToKeyMap(
            $productInAutosuggestionSnippetKeys
        );

        $this->pageBuilder->addSnippetsToPage($snippetCodeToKeyMap, $snippetKeyToContentMap);
    }

    /**
     * @param string[] $productInAutosuggestionSnippetKeys
     * @return string[]
     */
    private function getProductInAutosuggestionSnippetCodeToKeyMap($productInAutosuggestionSnippetKeys)
    {
        return array_reduce($productInAutosuggestionSnippetKeys, function (array $acc, $key) {
            $snippetCode = sprintf('product_%d', count($acc) + 1);
            $acc[$snippetCode] = $key;
            return $acc;
        }, []);
    }

    /**
     * @param string $searchQueryString
     */
    private function addSearchQueryStringSnippetToPageBuilder($searchQueryString)
    {
        $snippetCode = 'query_string';
        $snippetContent = $searchQueryString;

        $this->addDynamicSnippetToPageBuilder($snippetCode, $snippetContent);
    }

    /**
     * @param string $totalNumberOfResults
     */
    private function addTotalNumberOfResultsSnippetToPageBuilder($totalNumberOfResults)
    {
        $snippetCode = 'total_number_of_results';
        $snippetContent = $totalNumberOfResults;

        $this->addDynamicSnippetToPageBuilder($snippetCode, $snippetContent);
    }

    /**
     * @param string $snippetCode
     * @param string $snippetContent
     */
    private function addDynamicSnippetToPageBuilder($snippetCode, $snippetContent)
    {
        $snippetCodeToKeyMap = [$snippetCode => $snippetCode];
        $snippetKeyToContentMap = [$snippetCode => $snippetContent];

        $this->pageBuilder->addSnippetsToPage($snippetCodeToKeyMap, $snippetKeyToContentMap);
    }

    /**
     * @return ProductSearchAutosuggestionMetaSnippetContent
     */
    private function getMetaInfoSnippetContent()
    {
        $metaInfoSnippetKeyGenerator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode(
            ProductSearchAutosuggestionMetaSnippetRenderer::CODE
        );
        $metaInfoSnippetKey = $metaInfoSnippetKeyGenerator->getKeyForContext($this->context, []);
        $metaInfoSnippetJson = $this->dataPoolReader->getSnippet($metaInfoSnippetKey);

        return ProductSearchAutosuggestionMetaSnippetContent::fromJson($metaInfoSnippetJson);
    }

    /**
     * @param ProductId[] $productIds
     * @return string[]
     */
    private function getProductInAutosuggestionSnippetKeys(ProductId ...$productIds)
    {
        $keyGenerator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode(
            ProductInSearchAutosuggestionSnippetRenderer::CODE
        );

        return array_map(function (ProductId $productId) use ($keyGenerator) {
            return $keyGenerator->getKeyForContext($this->context, [Product::ID => $productId]);
        }, $productIds);
    }
}
