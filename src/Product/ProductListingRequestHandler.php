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
     * @var string[]
     */
    private $filterNavigationAttributeCodes;

    /**
     * @param Context $context
     * @param DataPoolReader $dataPoolReader
     * @param PageBuilder $pageBuilder
     * @param SnippetKeyGeneratorLocator $keyGeneratorLocator
     * @param BlockRenderer $filterNavigationBlockRenderer
     * @param string[] $filterNavigationAttributeCodes
     */
    public function __construct(
        Context $context,
        DataPoolReader $dataPoolReader,
        PageBuilder $pageBuilder,
        SnippetKeyGeneratorLocator $keyGeneratorLocator,
        BlockRenderer $filterNavigationBlockRenderer,
        array $filterNavigationAttributeCodes
    ) {
        $this->dataPoolReader = $dataPoolReader;
        $this->context = $context;
        $this->pageBuilder = $pageBuilder;
        $this->keyGeneratorLocator = $keyGeneratorLocator;
        $this->filterNavigationBlockRenderer = $filterNavigationBlockRenderer;
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

        $searchDocumentCollection = $this->getCollectionOfSearchDocumentsMatchingCriteria();
        $this->addFilterNavigationSnippetToPageBuilder($searchDocumentCollection);

        $filteredCollection = $this->applyFiltersToSearchDocumentCollection($searchDocumentCollection, $request);
        $this->addProductsInListingToPageBuilder($filteredCollection);

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
        if (0 === count($searchDocumentCollection)) {
            return;
        }

        $productInListingSnippetKeys = $this->getProductInListingSnippetKeysSearchDocumentCollection(
            $searchDocumentCollection
        );
        
        $snippetKeyToContentMap = $this->dataPoolReader->getSnippets($productInListingSnippetKeys);
        $snippetCodeToKeyMap = $this->getProductInListingSnippetCodeToKeyMap($productInListingSnippetKeys);

        $this->pageBuilder->addSnippetsToPage($snippetCodeToKeyMap, $snippetKeyToContentMap);
    }

    /**
     * @return SearchDocumentCollection
     */
    private function getCollectionOfSearchDocumentsMatchingCriteria()
    {
        $selectionCriteria = $this->pageMetaInfo->getSelectionCriteria();
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

    private function addFilterNavigationSnippetToPageBuilder(SearchDocumentCollection $searchDocumentCollection)
    {
        if (0 === count($searchDocumentCollection)) {
            return;
        }

        $dataObject = [
            'search_document_collection'        => $searchDocumentCollection,
            'filter_navigation_attribute_codes' => $this->filterNavigationAttributeCodes
        ];

        $snippetCode = 'filter_navigation';
        $snippetContents = $this->filterNavigationBlockRenderer->render($dataObject, $this->context);

        $snippetCodeToKeyMap = [$snippetCode => $snippetCode];
        $snippetKeyToContentMap = [$snippetCode => $snippetContents];

        $this->pageBuilder->addSnippetsToPage($snippetCodeToKeyMap, $snippetKeyToContentMap);
    }

    /**
     * @param SearchDocumentCollection $searchDocumentCollection
     * @param HttpRequest $request
     * @return SearchDocumentCollection
     */
    private function applyFiltersToSearchDocumentCollection(
        SearchDocumentCollection $searchDocumentCollection,
        HttpRequest $request
    ) {
        if (0 === count($searchDocumentCollection)) {
            return $searchDocumentCollection;
        }

        // TODO: Refactor this mess. Maybe move it into HttpRequest
        $filtersCriteria = SearchCriteria::createAnd();
        foreach ($this->filterNavigationAttributeCodes as $attributeCode) {
            $rawAttributeValue = $request->getQueryParameter($attributeCode);

            if (!trim($rawAttributeValue)) {
                continue;
            }

            $attributeValues = explode(',', $rawAttributeValue);

            $filterCriteria = SearchCriteria::createOr();
            foreach ($attributeValues as $attributeValue) {
                $filterCriteria->addCriterion(SearchCriterion::create($attributeCode, $attributeValue, '='));
            }
            $filtersCriteria->addCriteria($filterCriteria);
        }

        if (empty($filtersCriteria->getCriteria())) {
            return $searchDocumentCollection;
        }

        return $searchDocumentCollection->getCollectionFilteredByCriteria($filtersCriteria);
    }
}
