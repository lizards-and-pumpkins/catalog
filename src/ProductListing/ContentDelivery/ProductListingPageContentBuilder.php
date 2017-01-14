<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\ContentDelivery;

use LizardsAndPumpkins\Context\Locale\Locale;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\ProductSearchResult;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\SearchFieldToRequestParamMap;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageBuilder;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\ProductListing\Import\ProductListingRobotsMetaTagSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\ProductListingTemplateSnippetRenderer;
use LizardsAndPumpkins\Translation\TranslatorRegistry;

class ProductListingPageContentBuilder
{
    /**
     * @var PageBuilder
     */
    private $pageBuilder;

    /**
     * @var TranslatorRegistry
     */
    private $translatorRegistry;

    /**
     * @var SearchFieldToRequestParamMap
     */
    private $searchFieldToRequestParamMap;

    public function __construct(
        PageBuilder $pageBuilder,
        SearchFieldToRequestParamMap $searchFieldToRequestParamMap,
        TranslatorRegistry $translatorRegistry
    ) {
        $this->pageBuilder = $pageBuilder;
        $this->translatorRegistry = $translatorRegistry;
        $this->searchFieldToRequestParamMap = $searchFieldToRequestParamMap;
    }

    /**
     * @param PageMetaInfoSnippetContent $metaInfo
     * @param Context $context
     * @param mixed[] $keyGeneratorParams
     * @param ProductSearchResult $productSearchResult
     * @param ProductsPerPage $productsPerPage
     * @param SortBy $selectedSortBy
     * @param SortBy[] $availableSortBy
     * @return HttpResponse
     */
    public function buildPageContent(
        PageMetaInfoSnippetContent $metaInfo,
        Context $context,
        array $keyGeneratorParams,
        ProductSearchResult $productSearchResult,
        ProductsPerPage $productsPerPage,
        SortBy $selectedSortBy,
        SortBy ...$availableSortBy
    ) : HttpResponse {
        $this->addFilterNavigationSnippetToPageBuilder($productSearchResult);
        $this->addProductsInListingToPageBuilder($productSearchResult);
        $this->addPaginationSnippetsToPageBuilder($productSearchResult, $productsPerPage);
        $this->addSortOrderSnippetsToPageBuilder($selectedSortBy, ...$availableSortBy);
        $this->addTranslationsToPageBuilder($context);
        $this->addRobotsMetaTagSnippetToHeadContainer();

        return $this->pageBuilder->buildPage($metaInfo, $context, $keyGeneratorParams);
    }

    private function addFilterNavigationSnippetToPageBuilder(ProductSearchResult $productSearchResult)
    {
        $facetFields = $productSearchResult->getFacetFieldCollection()->jsonSerialize();
        $externalFacetFields = count($facetFields) > 0 ?
            $this->replaceInternalWithExternalFieldNames($facetFields) :
            [];

        $snippetCode = 'filter_navigation';
        $this->addDynamicSnippetToPageBuilder($snippetCode, json_encode($externalFacetFields));
    }

    /**
     * @param array[] $facetFields
     * @return array[]
     */
    private function replaceInternalWithExternalFieldNames(array $facetFields) : array
    {
        return array_reduce(array_keys($facetFields), function ($carry, $fieldName) use ($facetFields) {
            $parameterName = $this->searchFieldToRequestParamMap->getQueryParameterName($fieldName);
            return array_merge($carry, [$parameterName => $facetFields[$fieldName]]);
        }, []);
    }

    private function addProductsInListingToPageBuilder(ProductSearchResult $productSearchResult)
    {
        $this->addDynamicSnippetToPageBuilder('product_grid', json_encode($productSearchResult->getData()));
    }

    private function addPaginationSnippetsToPageBuilder(
        ProductSearchResult $productSearchResult,
        ProductsPerPage $productsPerPage
    ) {
        $this->addDynamicSnippetToPageBuilder(
            'total_number_of_results',
            $productSearchResult->getTotalNumberOfResults()
        );
        $this->addDynamicSnippetToPageBuilder('products_per_page', json_encode($productsPerPage));
    }

    private function addSortOrderSnippetsToPageBuilder(SortBy $selectedSortBy, SortBy ...$availableSortBy)
    {
        $this->addDynamicSnippetToPageBuilder('available_sort_orders', json_encode($availableSortBy));
        $this->addDynamicSnippetToPageBuilder('selected_sort_order', json_encode($selectedSortBy));
    }

    /**
     * @param string $snippetCode
     * @param string|int $snippetContents
     */
    private function addDynamicSnippetToPageBuilder(string $snippetCode, $snippetContents)
    {
        $snippetCodeToKeyMap = [$snippetCode => $snippetCode];
        $snippetKeyToContentMap = [$snippetCode => $snippetContents];

        $this->pageBuilder->addSnippetsToPage($snippetCodeToKeyMap, $snippetKeyToContentMap);
    }

    private function addTranslationsToPageBuilder(Context $context)
    {
        $translator = $this->translatorRegistry->getTranslator(
            ProductListingTemplateSnippetRenderer::CODE,
            $context->getValue(Locale::CONTEXT_CODE)
        );
        $this->addDynamicSnippetToPageBuilder('translations', json_encode($translator));
    }

    private function addRobotsMetaTagSnippetToHeadContainer()
    {
        $this->pageBuilder->addSnippetToContainer('head_container', ProductListingRobotsMetaTagSnippetRenderer::CODE);
    }
}
