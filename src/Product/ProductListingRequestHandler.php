<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\DataPool\DataPoolReader;
use Brera\DataPool\KeyValue\KeyNotFoundException;
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

        $originalSelectionCriteria = $this->pageMetaInfo->getSelectionCriteria();

        $selectedFilters = $this->getSelectedFilterValuesFromRequest($request);
        $selectionCriteriaWithSelectedFiltersApplied = $this->applyFiltersToSelectionCriteria(
            $originalSelectionCriteria,
            $selectedFilters
        );

        $searchDocumentCollection = $this->getCollectionOfSearchDocumentsMatchingCriteria(
            $selectionCriteriaWithSelectedFiltersApplied
        );

        if (0 < count($searchDocumentCollection)) {
            $this->filterNavigationFilterCollection->initialize(
                $searchDocumentCollection,
                $originalSelectionCriteria,
                $selectedFilters,
                $this->context
            );
            $this->addFilterNavigationSnippetToPageBuilder();
            $this->addProductsInListingToPageBuilder($searchDocumentCollection);
        }

        $keyGeneratorParams = [
            'products_per_page' => $this->getDefaultNumberOrProductsPerPage(),
            'url_key'           => ltrim($request->getUrlPathRelativeToWebFront(), '/')
        ];

        return $this->pageBuilder->buildPage($this->pageMetaInfo, $this->context, $keyGeneratorParams);
    }

    private function loadPageMetaInfoSnippet(HttpRequest $request)
    {
        if (is_null($this->pageMetaInfo)) {
            $this->pageMetaInfo = false;
            $metaInfoSnippetKey = $this->getMetaInfoSnippetKey($request);
            $json = $this->getPageMetaInfoJsonIfExists($metaInfoSnippetKey);
            if ($json) {
                $this->pageMetaInfo = ProductListingMetaInfoSnippetContent::fromJson($json);
            }
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
        $productInListingSnippetKeys = $this->getProductInListingSnippetKeysSearchDocumentCollection(
            $searchDocumentCollection
        );
        
        $snippetKeyToContentMap = $this->dataPoolReader->getSnippets($productInListingSnippetKeys);
        $snippetCodeToKeyMap = $this->getProductInListingSnippetCodeToKeyMap($productInListingSnippetKeys);

        $this->pageBuilder->addSnippetsToPage($snippetCodeToKeyMap, $snippetKeyToContentMap);
    }

    /**
     * @param SearchCriteria $selectionCriteria
     * @return SearchDocumentCollection
     */
    private function getCollectionOfSearchDocumentsMatchingCriteria(SearchCriteria $selectionCriteria)
    {
        return $this->dataPoolReader->getSearchDocumentsMatchingCriteria($selectionCriteria, $this->context);
    }

    /**
     * @param SearchDocumentCollection $collection
     * @return string[]
     */
    private function getProductInListingSnippetKeysSearchDocumentCollection(SearchDocumentCollection $collection)
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
     * @param SearchCriteria $originalSelectionCriteria
     * @param array[] $selectedFilters
     * @return SearchCriteria
     */
    private function applyFiltersToSelectionCriteria(SearchCriteria $originalSelectionCriteria, array $selectedFilters)
    {
        if (empty($selectedFilters)) {
            return $originalSelectionCriteria;
        }

        $filtersCriteria = SearchCriteria::createAnd();

        foreach ($selectedFilters as $filterCode => $filterValues) {
            if (empty($filterValues)) {
                continue;
            }

            $filterCriteria = SearchCriteria::createOr();
            foreach ($filterValues as $filterValue) {
                $filterCriteria->addCriterion(SearchCriterion::create($filterCode, $filterValue, '='));
            }
            $filtersCriteria->addCriteria($filterCriteria);
        }

        if (empty($filtersCriteria->getCriteria())) {
            return $originalSelectionCriteria;
        }

        $filtersCriteria->addCriteria($originalSelectionCriteria);

        return $filtersCriteria;
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
}
