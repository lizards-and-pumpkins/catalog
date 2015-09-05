<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\DataPool\DataPoolReader;
use Brera\DataPool\KeyValue\KeyNotFoundException;
use Brera\DataPool\SearchEngine\CompositeSearchCriterion;
use Brera\DataPool\SearchEngine\SearchCriteria;
use Brera\DataPool\SearchEngine\SearchCriterion;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocument;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestHandler;
use Brera\Http\HttpResponse;
use Brera\Http\UnableToHandleRequestException;
use Brera\PageBuilder;
use Brera\Renderer\BlockRenderer;
use Brera\SnippetKeyGeneratorLocator;

class ProductListingRequestHandler implements HttpRequestHandler
{
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
     * @var BlockRenderer
     */
    private $filterNavigationBlockRenderer;

    /**
     * @var FilterNavigationFilterCollection
     */
    private $filterNavigationFilterCollection;

    /**
     * @var array
     */
    private $filterNavigationAttributeCodes;

    /**
     * @param Context $context
     * @param DataPoolReader $dataPoolReader
     * @param PageBuilder $pageBuilder
     * @param SnippetKeyGeneratorLocator $keyGeneratorLocator
     * @param BlockRenderer $filterNavigationBlockRenderer
     * @param FilterNavigationFilterCollection $filterNavigationFilterCollection
     * @param string[] $filterNavigationAttributeCodes
     */
    public function __construct(
        Context $context,
        DataPoolReader $dataPoolReader,
        PageBuilder $pageBuilder,
        SnippetKeyGeneratorLocator $keyGeneratorLocator,
        BlockRenderer $filterNavigationBlockRenderer,
        FilterNavigationFilterCollection $filterNavigationFilterCollection,
        array $filterNavigationAttributeCodes
    ) {
        $this->dataPoolReader = $dataPoolReader;
        $this->context = $context;
        $this->pageBuilder = $pageBuilder;
        $this->keyGeneratorLocator = $keyGeneratorLocator;
        $this->filterNavigationBlockRenderer = $filterNavigationBlockRenderer;
        $this->filterNavigationFilterCollection = $filterNavigationFilterCollection;
        $this->filterNavigationAttributeCodes = $filterNavigationAttributeCodes;
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

        $this->addProductListingContentToPage($documentCollection, $originalCriteria, $selectedFilters);

        $keyGeneratorParams = [
            'products_per_page' => $this->getDefaultNumberOrProductsPerPage(),
            'url_key'           => ltrim($request->getUrlPathRelativeToWebFront(), '/')
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

    private function addProductsInListingToPageBuilder(SearchDocumentCollection $searchDocumentCollection)
    {
        $productInListingSnippetKeys = $this->getProductInListingSnippetKeysForSearchDocumentCollection(
            $searchDocumentCollection
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
     * @param SearchDocumentCollection $collection
     * @return string[]
     */
    private function getProductInListingSnippetKeysForSearchDocumentCollection(SearchDocumentCollection $collection)
    {
        $snippetCode = ProductInListingSnippetRenderer::CODE;
        $keyGenerator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode($snippetCode);
        return array_map(function (SearchDocument $searchDocument) use ($keyGenerator) {
            return $keyGenerator->getKeyForContext($this->context, ['product_id' => $searchDocument->getProductId()]);
        }, $collection->getDocuments());
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
     * @return string
     */
    private function getDefaultNumberOrProductsPerPage()
    {
        $keyGenerator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode(
            DefaultNumberOfProductsPerPageSnippetRenderer::CODE
        );
        $snippetKey = $keyGenerator->getKeyForContext($this->context, []);
        $defaultNumberOrProductsPerPage = $this->dataPoolReader->getSnippet($snippetKey);

        return $defaultNumberOrProductsPerPage;
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
        $metaInfoSnippetKey = $keyGenerator->getKeyForContext($this->context, ['url_key' => $urlKey]);

        return $metaInfoSnippetKey;
    }

    private function addFilterNavigationSnippetToPageBuilder()
    {
        $dataObject = $this->filterNavigationFilterCollection;

        $snippetCode = 'filter_navigation';
        $snippetContents = $this->filterNavigationBlockRenderer->render($dataObject, $this->context);

        $snippetCodeToKeyMap = [$snippetCode => $snippetCode];
        $snippetKeyToContentMap = [$snippetCode => $snippetContents];

        $this->pageBuilder->addSnippetsToPage($snippetCodeToKeyMap, $snippetKeyToContentMap);
    }

    /**
     * @param SearchCriteria $originalCriteria
     * @param array[] $filters
     * @return SearchCriteria
     */
    private function applyFiltersToSelectionCriteria(SearchCriteria $originalCriteria, array $filters)
    {
        if (empty($filters)) {
            return $originalCriteria;
        }

        $criteriaWithFiltersApplied = CompositeSearchCriterion::createAnd();
        $somethingIsAdded = false;

        foreach ($filters as $filterCode => $filterValues) {
            if (empty($filterValues)) {
                continue;
            }

            $filterCriteria = CompositeSearchCriterion::createOr();
            foreach ($filterValues as $filterValue) {
                $filterCriteria->addCriteria(SearchCriterion::create($filterCode, $filterValue, '='));
            }
            $criteriaWithFiltersApplied->addCriteria($filterCriteria);
            $somethingIsAdded = true;
        }

        if (false === $somethingIsAdded) {
            return $originalCriteria;
        }

        $criteriaWithFiltersApplied->addCriteria($originalCriteria);

        return $criteriaWithFiltersApplied;
    }

    /**
     * @param HttpRequest $request
     * @return array[]
     */
    private function getSelectedFilterValuesFromRequest(HttpRequest $request)
    {
        $selectedFilters = [];

        foreach ($this->filterNavigationAttributeCodes as $filterCode) {
            $rawAttributeValue = $request->getQueryParameter($filterCode);
            $selectedFilters[$filterCode] = array_filter(explode(',', $rawAttributeValue));
        }

        return $selectedFilters;
    }

    /**
     * @param SearchDocumentCollection $searchDocumentCollection
     * @param SearchCriteria $originalCriteria
     * @param array[] $selectedFilters
     */
    private function addProductListingContentToPage(
        SearchDocumentCollection $searchDocumentCollection,
        SearchCriteria $originalCriteria,
        array $selectedFilters
    ) {
        if (1 > count($searchDocumentCollection)) {
            return;
        }

        $this->addFilterNavigationToPageBuilder($searchDocumentCollection, $originalCriteria, $selectedFilters);
        $this->addProductsInListingToPageBuilder($searchDocumentCollection);
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
}
