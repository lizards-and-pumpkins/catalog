<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\DataPool\KeyValue\KeyNotFoundException;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestHandler;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\UnableToHandleRequestException;
use LizardsAndPumpkins\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Product\ProductListingCriteriaSnippetContent;
use LizardsAndPumpkins\Product\ProductListingCriteriaSnippetRenderer;

class ProductListingRequestHandler implements HttpRequestHandler
{
    use ProductListingRequestHandlerTrait;

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
        return false !== $this->getPageMetaInfoSnippet($request);
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

        $searchEngineResponse = $this->getSearchResultsMatchingCriteria($request);
        $this->addProductListingContentToPage($searchEngineResponse);

        $metaInfo = $this->getPageMetaInfoSnippet($request);
        $keyGeneratorParams = [
            'products_per_page' => $this->defaultNumberOfProductsPerPage,
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
     * @return SearchEngineResponse
     */
    private function getSearchResultsMatchingCriteria(HttpRequest $request)
    {
        $selectedFilters = $this->getSelectedFilterValuesFromRequest($request);
        $originalCriteria = $this->getPageMetaInfoSnippet($request)->getSelectionCriteria();

        $criteria = $this->applyFiltersToSelectionCriteria($originalCriteria, $selectedFilters);

        $currentPageNumber = $this->getCurrentPageNumber($request);
        $productsPerPage = (int) $this->defaultNumberOfProductsPerPage;

        return $this->dataPoolReader->getSearchResultsMatchingCriteria(
            $criteria,
            $this->context,
            $this->filterNavigationAttributeCodes,
            $productsPerPage,
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
                return $this->searchCriteriaBuilder->fromRequestParameter($filterCode, $filterOptionValue);
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
}
