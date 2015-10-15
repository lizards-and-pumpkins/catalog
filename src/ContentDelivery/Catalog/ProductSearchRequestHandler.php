<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestHandler;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\UnableToHandleRequestException;
use LizardsAndPumpkins\Product\ProductSearchResultMetaSnippetContent;
use LizardsAndPumpkins\Product\ProductSearchResultMetaSnippetRenderer;

class ProductSearchRequestHandler implements HttpRequestHandler
{
    use ProductListingRequestHandlerTrait;

    const SEARCH_RESULTS_SLUG = 'catalogsearch/result';
    const QUERY_STRING_PARAMETER_NAME = 'q';
    const SEARCH_QUERY_MINIMUM_LENGTH = 3;

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
        if (!$this->canProcess($request)) {
            throw new UnableToHandleRequestException(sprintf('Unable to process request with handler %s', __CLASS__));
        }

        $searchEngineResponse = $this->getSearchResults($request);
        $this->addProductListingContentToPage($searchEngineResponse);

        $metaInfoSnippetKeyGenerator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode(
            ProductSearchResultMetaSnippetRenderer::CODE
        );
        $metaInfoSnippetKey = $metaInfoSnippetKeyGenerator->getKeyForContext($this->context, []);
        $metaInfoSnippetJson = $this->dataPoolReader->getSnippet($metaInfoSnippetKey);
        $metaInfoSnippetContent = ProductSearchResultMetaSnippetContent::fromJson($metaInfoSnippetJson);

        $keyGeneratorParams = [
            'products_per_page' => $this->defaultNumberOfProductsPerPage
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
     * @param HttpRequest $request
     * @return SearchEngineResponse
     */
    private function getSearchResults(HttpRequest $request)
    {
        $queryString = $request->getQueryParameter(self::QUERY_STRING_PARAMETER_NAME);
        $currentPageNumber = $this->getCurrentPageNumber($request);

        $rowsPerPage = $this->defaultNumberOfProductsPerPage;
        $pageNumber = $currentPageNumber;

        return $this->dataPoolReader->getSearchResults(
            $queryString,
            $this->context,
            $this->filterNavigationAttributeCodes,
            $rowsPerPage,
            $pageNumber
        );
    }

    /* TODO: Apply selected filters to criteria */
}
