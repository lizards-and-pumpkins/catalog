<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\KeyValue\KeyNotFoundException;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\Http\Exception\UnableToHandleRequestException;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestHandler;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\PageBuilder;
use LizardsAndPumpkins\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Product\ProductListingCriteriaSnippetContent;
use LizardsAndPumpkins\Product\ProductListingCriteriaSnippetRenderer;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator;

class ProductListingRequestHandler implements HttpRequestHandler
{
    use ProductListingRequestHandlerTrait;

    const PRODUCTS_PER_PAGE_COOKIE_NAME = 'products_per_page';
    const PRODUCTS_PER_PAGE_COOKIE_TTL = 3600 * 24 * 30;
    const PRODUCTS_PER_PAGE_QUERY_PARAMETER_NAME = 'limit';

    /**
     * @param Context $context
     * @param DataPoolReader $dataPoolReader
     * @param PageBuilder $pageBuilder
     * @param SnippetKeyGeneratorLocator $keyGeneratorLocator
     * @param string[] $filterNavigationConfig
     * @param ProductsPerPage $productsPerPage
     */
    public function __construct(
        Context $context,
        DataPoolReader $dataPoolReader,
        PageBuilder $pageBuilder,
        SnippetKeyGeneratorLocator $keyGeneratorLocator,
        array $filterNavigationConfig,
        ProductsPerPage $productsPerPage
    ) {
        $this->dataPoolReader = $dataPoolReader;
        $this->context = $context;
        $this->pageBuilder = $pageBuilder;
        $this->keyGeneratorLocator = $keyGeneratorLocator;
        $this->filterNavigationConfig = $filterNavigationConfig;
        $this->productsPerPage = $productsPerPage;
    }

    /**
     * @var ProductListingCriteriaSnippetContent|bool
     */
    private $lazyLoadedPageMetaInfo;

    /**
     * @param HttpRequest $request
     * @return bool
     */
    public function canProcess(HttpRequest $request)
    {
        return $this->getPageMetaInfoSnippet($request) !== false;
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

        $this->processCookies($request);

        $productsPerPage = $this->getProductsPerPage($request);
        $searchEngineResponse = $this->getSearchResultsMatchingCriteria($request, $productsPerPage);
        $this->addProductListingContentToPage($searchEngineResponse, $productsPerPage);

        $metaInfo = $this->getPageMetaInfoSnippet($request);
        $keyGeneratorParams = [
            PageMetaInfoSnippetContent::URL_KEY => ltrim($request->getUrlPathRelativeToWebFront(), '/')
        ];

        return $this->pageBuilder->buildPage($metaInfo, $this->context, $keyGeneratorParams);
    }

    /**
     * @param HttpRequest $request
     * @return bool|ProductListingCriteriaSnippetContent
     */
    private function getPageMetaInfoSnippet(HttpRequest $request)
    {
        if (null === $this->lazyLoadedPageMetaInfo) {
            $this->lazyLoadedPageMetaInfo = false;
            $metaInfoSnippetKey = $this->getMetaInfoSnippetKey($request);
            $json = $this->getPageMetaInfoJsonIfExists($metaInfoSnippetKey);
            if ($json) {
                $this->lazyLoadedPageMetaInfo = ProductListingCriteriaSnippetContent::fromJson($json);
            }
        }

        return $this->lazyLoadedPageMetaInfo;
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

    /**
     * @param HttpRequest $request
     * @param ProductsPerPage $productsPerPage
     * @return SearchEngineResponse
     */
    private function getSearchResultsMatchingCriteria(HttpRequest $request, ProductsPerPage $productsPerPage)
    {
        $criteria = $this->getPageMetaInfoSnippet($request)->getSelectionCriteria();
        $selectedFilters = $this->getSelectedFilterValuesFromRequest($request);
        $currentPageNumber = $this->getCurrentPageNumber($request);

        return $this->dataPoolReader->getSearchResultsMatchingCriteria(
            $criteria,
            $selectedFilters,
            $this->context,
            $this->filterNavigationConfig,
            $productsPerPage->getSelectedNumberOfProductsPerPage(),
            $currentPageNumber
        );
    }

    /**
     * @param HttpRequest $request
     * @return string
     */
    private function getMetaInfoSnippetKey(HttpRequest $request)
    {
        $keyGenerator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode(
            ProductListingCriteriaSnippetRenderer::CODE
        );
        $urlKey = $request->getUrlPathRelativeToWebFront();
        $metaInfoSnippetKey = $keyGenerator->getKeyForContext(
            $this->context,
            [PageMetaInfoSnippetContent::URL_KEY => $urlKey]
        );

        return $metaInfoSnippetKey;
    }
}
