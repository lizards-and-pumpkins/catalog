<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\DataPool\DataPoolReader;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestHandler;
use Brera\Http\HttpResponse;
use Brera\Http\UnableToHandleRequestException;
use Brera\PageBuilder;
use Brera\SnippetKeyGeneratorLocator;

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
            throw new UnableToHandleRequestException;
        }

        $searchQueryString = $request->getUrl()->getQueryParameter(self::QUERY_STRING_PARAMETER_NAME);

        $this->addSearchResultsToPageBuilder($searchQueryString);

        $metaInfoSnippetKeyGenerator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode(
            ProductSearchResultsMetaSnippetRenderer::CODE
        );
        $metaInfoSnippetKey = $metaInfoSnippetKeyGenerator->getKeyForContext($this->context, []);
        $metaInfoSnippetJson = $this->dataPoolReader->getSnippet($metaInfoSnippetKey);
        $metaInfoSnippetContent = ProductSearchResultsMetaSnippetContent::fromJson($metaInfoSnippetJson);

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
        $urlPathWithoutTrailingSlash = rtrim($request->getUrl()->getPathRelativeToWebFront(), '/');

        if (self::SEARCH_RESULTS_SLUG !== $urlPathWithoutTrailingSlash) {
            return false;
        }

        if (HttpRequest::METHOD_GET !== $request->getMethod()) {
            return false;
        }

        $searchQueryString = $request->getUrl()->getQueryParameter(self::QUERY_STRING_PARAMETER_NAME);

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
        $productIds = $this->dataPoolReader->getSearchResults($queryString, $this->context);

        if (empty($productIds)) {
            return;
        }

        $keyGenerator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode(
            ProductInListingInContextSnippetRenderer::CODE
        );
        $productInListingSnippetKeys = array_map(function ($productId) use ($keyGenerator) {
            return $keyGenerator->getKeyForContext($this->context, ['product_id' => $productId]);
        }, $productIds);

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
