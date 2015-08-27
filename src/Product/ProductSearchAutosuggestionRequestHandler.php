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
            throw new UnableToHandleRequestException('Unable to handle ProductSearchAutosuggestion request.');
        }

        $searchQueryString = $request->getUrl()->getQueryParameter(self::QUERY_STRING_PARAMETER_NAME);
        $productIds = $this->dataPoolReader->getSearchResults($searchQueryString, $this->context);

        $this->addSearchResultsToPageBuilder($productIds);

        $metaInfoSnippetContent = $this->getMetaInfoSnippetContent();

        $this->addTotalNumberOfResultsSnippetToPageBuilder(count($productIds));
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
     * @param string[] $productIds
     */
    private function addSearchResultsToPageBuilder(array $productIds)
    {
        if (empty($productIds)) {
            return;
        }

        $keyGenerator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode(
            ProductInSearchAutosuggestionSnippetRenderer::CODE
        );
        $productInAutosuggestionSnippetKeys = array_map(function ($productId) use ($keyGenerator) {
            return $keyGenerator->getKeyForContext($this->context, ['product_id' => $productId]);
        }, $productIds);

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
