<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\KeyValue\KeyNotFoundException;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestHandler;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\UnableToHandleRequestException;
use LizardsAndPumpkins\PageBuilder;
use LizardsAndPumpkins\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator;

class ProductListingRequestHandler implements HttpRequestHandler
{
    const PAGINATION_QUERY_PARAMETER_NAME = 'p';

    /**
     * @var ProductListingMetaInfoSnippetContent
     */
    private $pageMetaInfo;

    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var PageBuilder
     */
    private $pageBuilder;

    /**
     * @var SnippetKeyGeneratorLocator
     */
    private $keyGeneratorLocator;

    /**
     * @var FilterNavigationFilterCollection
     */
    private $filterNavigationFilterCollection;

    /**
     * @var string[]
     */
    private $filterNavigationAttributeCodes;

    /**
     * @var
     */
    private $defaultNumberOfProductsPerPage;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param Context $context
     * @param DataPoolReader $dataPoolReader
     * @param PageBuilder $pageBuilder
     * @param SnippetKeyGeneratorLocator $keyGeneratorLocator
     * @param FilterNavigationFilterCollection $filterNavigationFilterCollection
     * @param string[] $filterNavigationAttributeCodes
     * @param int $defaultNumberOfProductsPerPage
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Context $context,
        DataPoolReader $dataPoolReader,
        PageBuilder $pageBuilder,
        SnippetKeyGeneratorLocator $keyGeneratorLocator,
        FilterNavigationFilterCollection $filterNavigationFilterCollection,
        array $filterNavigationAttributeCodes,
        $defaultNumberOfProductsPerPage,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->dataPoolReader = $dataPoolReader;
        $this->context = $context;
        $this->pageBuilder = $pageBuilder;
        $this->keyGeneratorLocator = $keyGeneratorLocator;
        $this->filterNavigationFilterCollection = $filterNavigationFilterCollection;
        $this->filterNavigationAttributeCodes = $filterNavigationAttributeCodes;
        $this->defaultNumberOfProductsPerPage = $defaultNumberOfProductsPerPage;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param HttpRequest $request
     * @return bool
     */
    public function canProcess(HttpRequest $request)
    {
        $this->loadPageMetaInfoSnippet($request);
        return (bool)$this->pageMetaInfo;
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

        $selectedFilters = $this->getSelectedFilterValuesFromRequest($request);
        $originalCriteria = $this->pageMetaInfo->getSelectionCriteria();

        $documentCollection = $this->getSearchDocumentsMatchingCriteria($originalCriteria, $selectedFilters);

        $this->addProductListingContentToPage($documentCollection, $originalCriteria, $request, $selectedFilters);

        $keyGeneratorParams = [
            'products_per_page' => $this->defaultNumberOfProductsPerPage,
            PageMetaInfoSnippetContent::URL_KEY => ltrim($request->getUrlPathRelativeToWebFront(), '/')
        ];

        return $this->pageBuilder->buildPage($this->pageMetaInfo, $this->context, $keyGeneratorParams);
    }

    private function loadPageMetaInfoSnippet(HttpRequest $request)
    {
        if (null !== $this->pageMetaInfo) {
            return;
        }

        $this->pageMetaInfo = false;
        $metaInfoSnippetKey = $this->getMetaInfoSnippetKey($request);
        $json = $this->getPageMetaInfoJsonIfExists($metaInfoSnippetKey);
        if ($json) {
            $this->pageMetaInfo = ProductListingMetaInfoSnippetContent::fromJson($json);
        }
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

    private function addProductsInListingToPageBuilder(
        SearchDocumentCollection $searchDocumentCollection,
        HttpRequest $request
    ) {
        $currentPageNumber = $request->getQueryParameter(self::PAGINATION_QUERY_PARAMETER_NAME);
        $productsPerPage = (int)$this->defaultNumberOfProductsPerPage;

        $documents = $searchDocumentCollection->getDocuments();
        $currentPageDocuments = array_slice($documents, ($currentPageNumber - 1) * $productsPerPage, $productsPerPage);

        $productInListingSnippetKeys = $this->getProductInListingSnippetKeysForSearchDocuments(
            ...$currentPageDocuments
        );

        $snippetKeyToContentMap = $this->dataPoolReader->getSnippets($productInListingSnippetKeys);
        $snippetCodeToKeyMap = $this->getProductInListingSnippetCodeToKeyMap($productInListingSnippetKeys);

        $this->pageBuilder->addSnippetsToPage($snippetCodeToKeyMap, $snippetKeyToContentMap);
    }

    /**
     * @param SearchCriteria $originalCriteria
     * @param array[] $selectedFilters
     * @return SearchDocumentCollection
     */
    private function getSearchDocumentsMatchingCriteria(SearchCriteria $originalCriteria, array $selectedFilters)
    {
        $criteriaWithSelectedFiltersApplied = $this->applyFiltersToSelectionCriteria(
            $originalCriteria,
            $selectedFilters
        );

        $searchDocumentCollection = $this->dataPoolReader->getSearchDocumentsMatchingCriteria(
            $criteriaWithSelectedFiltersApplied,
            $this->context
        );

        return $searchDocumentCollection;
    }

    /**
     * @param SearchDocument ...$searchDocuments
     * @return string[]
     */
    private function getProductInListingSnippetKeysForSearchDocuments(SearchDocument ...$searchDocuments)
    {
        $keyGenerator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode(
            ProductInListingSnippetRenderer::CODE
        );
        return array_map(function (SearchDocument $searchDocument) use ($keyGenerator) {
            return $keyGenerator->getKeyForContext($this->context, [Product::ID => $searchDocument->getProductId()]);
        }, $searchDocuments);
    }

    /**
     * @param string[] $productInListingSnippetKeys
     * @return string[]
     */
    private function getProductInListingSnippetCodeToKeyMap($productInListingSnippetKeys)
    {
        return array_reduce($productInListingSnippetKeys, function (array $acc, $key) {
            $snippetCode = sprintf('product_%d', count($acc) + 1);
            $acc[$snippetCode] = $key;
            return $acc;
        }, []);
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    private function getMetaInfoSnippetKey(HttpRequest $request)
    {
        $keyGenerator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode(
            ProductListingMetaInfoSnippetRenderer::CODE
        );
        $urlKey = $request->getUrlPathRelativeToWebFront();
        $metaInfoSnippetKey = $keyGenerator->getKeyForContext(
            $this->context,
            [PageMetaInfoSnippetContent::URL_KEY => $urlKey]
        );

        return $metaInfoSnippetKey;
    }

    /**
     * @param SearchCriteria $originalCriteria
     * @param array[] $filters
     * @return SearchCriteria
     */
    private function applyFiltersToSelectionCriteria(SearchCriteria $originalCriteria, array $filters)
    {
        $filtersCriteriaArray = [];

        foreach ($filters as $filterCode => $filterOptionValues) {
            if (empty($filterOptionValues)) {
                continue;
            }

            $optionValuesCriteriaArray = array_map(function ($filterOptionValue) use ($filterCode) {
                return $this->searchCriteriaBuilder->create($filterCode, $filterOptionValue);
            }, $filterOptionValues);

            $filterCriteria = CompositeSearchCriterion::createOr(...$optionValuesCriteriaArray);
            $filtersCriteriaArray[] = $filterCriteria;
        }

        if (empty($filtersCriteriaArray)) {
            return $originalCriteria;
        }

        $filtersCriteriaArray[] = $originalCriteria;
        return CompositeSearchCriterion::createAnd(...$filtersCriteriaArray);
    }

    /**
     * @param HttpRequest $request
     * @return array[]
     */
    private function getSelectedFilterValuesFromRequest(HttpRequest $request)
    {
        return array_reduce($this->filterNavigationAttributeCodes, function ($carry, $attributeCode) use ($request) {
            $carry[$attributeCode] = array_filter(explode(',', $request->getQueryParameter($attributeCode)));
            return $carry;
        }, []);
    }

    /**
     * @param SearchDocumentCollection $searchDocumentCollection
     * @param SearchCriteria $originalCriteria
     * @param HttpRequest $request
     * @param array[] $selectedFilters
     */
    private function addProductListingContentToPage(
        SearchDocumentCollection $searchDocumentCollection,
        SearchCriteria $originalCriteria,
        HttpRequest $request,
        array $selectedFilters
    ) {
        if (0 === count($searchDocumentCollection)) {
            return;
        }

        $this->addFilterNavigationToPageBuilder($searchDocumentCollection, $originalCriteria, $selectedFilters);
        $this->addProductsInListingToPageBuilder($searchDocumentCollection, $request);
        $this->addPaginationToPageBuilder($searchDocumentCollection);
        $this->addCollectionSizeToPageBuilder($searchDocumentCollection);
    }

    /**
     * @param SearchDocumentCollection $searchDocumentCollection
     * @param SearchCriteria $originalCriteria
     * @param array[] $selectedFilters
     */
    private function addFilterNavigationToPageBuilder(
        SearchDocumentCollection $searchDocumentCollection,
        SearchCriteria $originalCriteria,
        array $selectedFilters
    ) {
        $this->filterNavigationFilterCollection->initialize(
            $searchDocumentCollection,
            $originalCriteria,
            $selectedFilters,
            $this->context
        );
        $this->addFilterNavigationSnippetToPageBuilder();
    }

    private function addFilterNavigationSnippetToPageBuilder()
    {
        $snippetCode = 'filter_navigation';
        $snippetContents = json_encode($this->filterNavigationFilterCollection, JSON_PRETTY_PRINT);

        $this->addDynamicSnippetToPageBuilder($snippetCode, $snippetContents);
    }

    private function addPaginationToPageBuilder(SearchDocumentCollection $searchDocumentCollection)
    {
        $numberOfProductsPerPage = (int)$this->defaultNumberOfProductsPerPage;
        $totalPagesCount = ceil(count($searchDocumentCollection) / $numberOfProductsPerPage);
        $this->addDynamicSnippetToPageBuilder('total_pages_count', $totalPagesCount);
    }

    private function addCollectionSizeToPageBuilder(SearchDocumentCollection $searchDocumentCollection)
    {
        $snippetCode = 'collection_size';
        $snippetContent = count($searchDocumentCollection);

        $this->addDynamicSnippetToPageBuilder($snippetCode, $snippetContent);
    }

    /**
     * @param string $snippetCode
     * @param string $snippetContents
     */
    private function addDynamicSnippetToPageBuilder($snippetCode, $snippetContents)
    {
        $snippetCodeToKeyMap = [$snippetCode => $snippetCode];
        $snippetKeyToContentMap = [$snippetCode => $snippetContents];

        $this->pageBuilder->addSnippetsToPage($snippetCodeToKeyMap, $snippetKeyToContentMap);
    }
}
