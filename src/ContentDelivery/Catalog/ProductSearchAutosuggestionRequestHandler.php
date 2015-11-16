<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestHandler;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\Exception\UnableToHandleRequestException;
use LizardsAndPumpkins\PageBuilder;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductInSearchAutosuggestionSnippetRenderer;
use LizardsAndPumpkins\Product\ProductSearchAutosuggestionMetaSnippetContent;
use LizardsAndPumpkins\Product\ProductSearchAutosuggestionMetaSnippetRenderer;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator\SnippetKeyGeneratorLocator;

class ProductSearchAutosuggestionRequestHandler implements HttpRequestHandler
{
    const SEARCH_RESULTS_SLUG = 'catalogsearch/suggest';
    const QUERY_STRING_PARAMETER_NAME = 'q';
    const SEARCH_QUERY_MINIMUM_LENGTH = 3;

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
     * @var string[]
     */
    private $searchableAttributeCodes;

    /**
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var SortOrderConfig
     */
    private $sortOrderConfig;

    /**
     * ProductSearchAutosuggestionRequestHandler constructor.
     *
     * @param Context $context
     * @param DataPoolReader $dataPoolReader
     * @param PageBuilder $pageBuilder
     * @param SnippetKeyGeneratorLocator $keyGeneratorLocator
     * @param SearchCriteriaBuilder $criteriaBuilder
     * @param string[] $searchableAttributeCodes
     * @param SortOrderConfig $sortOrderConfig
     */
    public function __construct(
        Context $context,
        DataPoolReader $dataPoolReader,
        PageBuilder $pageBuilder,
        SnippetKeyGeneratorLocator $keyGeneratorLocator,
        SearchCriteriaBuilder $criteriaBuilder,
        array $searchableAttributeCodes,
        SortOrderConfig $sortOrderConfig
    ) {
        $this->context = $context;
        $this->dataPoolReader = $dataPoolReader;
        $this->pageBuilder = $pageBuilder;
        $this->keyGeneratorLocator = $keyGeneratorLocator;
        $this->criteriaBuilder = $criteriaBuilder;
        $this->searchableAttributeCodes = $searchableAttributeCodes;
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
        $searchDocumentsCollection = $this->getSearchResults($searchQueryString);
        $this->addSearchResultsToPageBuilder($searchDocumentsCollection);

        $metaInfoSnippetContent = $this->getMetaInfoSnippetContent();

        $this->addTotalNumberOfResultsSnippetToPageBuilder(count($searchDocumentsCollection));
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
     * @param string $queryString
     * @return SearchDocumentCollection
     */
    private function getSearchResults($queryString)
    {
        $criteria = $this->criteriaBuilder->anyOfFieldsContainString($this->searchableAttributeCodes, $queryString);
        $selectedFilters = [];
        $facetFields = [];
        $rowsPerPage = 100; // TODO: Replace with configured number of suggestions to show
        $pageNumber = 0;
        $searchEngineResponse = $this->dataPoolReader->getSearchResultsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $this->context,
            $facetFields,
            $rowsPerPage,
            $pageNumber,
            $this->sortOrderConfig
        );
        $searchDocumentsCollection = $searchEngineResponse->getSearchDocuments();

        return $searchDocumentsCollection;
    }

    private function addSearchResultsToPageBuilder(SearchDocumentCollection $searchDocumentCollection)
    {
        if (0 === count($searchDocumentCollection)) {
            return;
        }

        $productInAutosuggestionSnippetKeys = $this->getProductInAutosuggestionSnippetKeys($searchDocumentCollection);
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
     * @param SearchDocumentCollection $searchDocumentCollection
     * @return string[]
     */
    private function getProductInAutosuggestionSnippetKeys(SearchDocumentCollection $searchDocumentCollection)
    {
        $keyGenerator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode(
            ProductInSearchAutosuggestionSnippetRenderer::CODE
        );

        return array_map(function (SearchDocument $searchDocument) use ($keyGenerator) {
            return $keyGenerator->getKeyForContext($this->context, [Product::ID => $searchDocument->getProductId()]);
        }, $searchDocumentCollection->getDocuments());
    }
}
