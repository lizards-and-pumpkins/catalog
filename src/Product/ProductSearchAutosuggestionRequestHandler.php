<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\DataPool\DataPoolReader;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocument;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestHandler;
use Brera\Http\HttpResponse;
use Brera\Http\UnableToHandleRequestException;
use Brera\PageBuilder;
use Brera\SnippetKeyGeneratorLocator;

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

    public function __construct(
        Context $context,
        DataPoolReader $dataPoolReader,
        PageBuilder $pageBuilder,
        SnippetKeyGeneratorLocator $keyGeneratorLocator
    ) {
        $this->context = $context;
        $this->dataPoolReader = $dataPoolReader;
        $this->pageBuilder = $pageBuilder;
        $this->keyGeneratorLocator = $keyGeneratorLocator;
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
        $searchDocumentsCollection = $this->dataPoolReader->getSearchResults($searchQueryString, $this->context);

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

    private function addSearchResultsToPageBuilder(SearchDocumentCollection $searchDocumentCollection)
    {
        if (0 === count($searchDocumentCollection)) {
            return;
        }

        $keyGenerator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode(
            ProductInSearchAutosuggestionSnippetRenderer::CODE
        );
        $searchDocuments = $searchDocumentCollection->getDocuments();
        $productInAutosuggestionSnippetKeys = array_map(function (SearchDocument $searchDocument) use ($keyGenerator) {
            return $keyGenerator->getKeyForContext($this->context, ['product_id' => $searchDocument->getProductId()]);
        }, $searchDocuments);

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
}
