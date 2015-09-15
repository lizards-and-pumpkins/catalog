<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestHandler;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\UnableToHandleRequestException;
use LizardsAndPumpkins\PageBuilder;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator;

class ProductSearchRequestHandler implements HttpRequestHandler
{
    const SEARCH_RESULTS_SLUG = 'catalogsearch/result';
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

        $this->addSearchResultsToPageBuilder($searchQueryString);

        $metaInfoSnippetKeyGenerator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode(
            ProductSearchResultMetaSnippetRenderer::CODE
        );
        $metaInfoSnippetKey = $metaInfoSnippetKeyGenerator->getKeyForContext($this->context, []);
        $metaInfoSnippetJson = $this->dataPoolReader->getSnippet($metaInfoSnippetKey);
        $metaInfoSnippetContent = ProductSearchResultMetaSnippetContent::fromJson($metaInfoSnippetJson);

        $keyGeneratorParams = [
            'items_per_page' => $this->getDefaultNumberOrProductsPerPage(),
            'query_string'   => $searchQueryString,
        ];

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
     */
    private function addSearchResultsToPageBuilder($queryString)
    {
        $searchDocumentCollection = $this->dataPoolReader->getSearchResults($queryString, $this->context);

        if (empty($searchDocumentCollection)) {
            return;
        }

        $keyGenerator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode(
            ProductInListingSnippetRenderer::CODE
        );
        $productInListingSnippetKeys = array_map(function (SearchDocument $searchDocument) use ($keyGenerator) {
            return $keyGenerator->getKeyForContext($this->context, [Product::ID => $searchDocument->getProductId()]);
        }, $searchDocumentCollection->getDocuments());

        $snippetKeyToContentMap = $this->dataPoolReader->getSnippets($productInListingSnippetKeys);
        $snippetCodeToKeyMap = $this->getProductInListingSnippetCodeToKeyMap($productInListingSnippetKeys);

        $this->pageBuilder->addSnippetsToPage($snippetCodeToKeyMap, $snippetKeyToContentMap);
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
}
