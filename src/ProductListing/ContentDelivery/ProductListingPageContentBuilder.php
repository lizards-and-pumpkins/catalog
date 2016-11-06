<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\ContentDelivery;

use LizardsAndPumpkins\Context\Locale\Locale;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderConfig;
use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonService;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\SearchFieldToRequestParamMap;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageBuilder;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Import\Product\ProductId;
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
     * @var SortOrderConfig[]
     */
    private $sortOrderConfigs;

    /**
     * @var ProductJsonService
     */
    private $productJsonService;

    /**
     * @var SearchFieldToRequestParamMap
     */
    private $searchFieldToRequestParamMap;

    public function __construct(
        ProductJsonService $productJsonService,
        PageBuilder $pageBuilder,
        SearchFieldToRequestParamMap $searchFieldToRequestParamMap,
        TranslatorRegistry $translatorRegistry,
        SortOrderConfig ...$sortOrderConfigs
    ) {
        $this->productJsonService = $productJsonService;
        $this->pageBuilder = $pageBuilder;
        $this->sortOrderConfigs = $sortOrderConfigs;
        $this->translatorRegistry = $translatorRegistry;
        $this->searchFieldToRequestParamMap = $searchFieldToRequestParamMap;
    }

    /**
     * @param PageMetaInfoSnippetContent $metaInfo
     * @param Context $context
     * @param mixed[] $keyGeneratorParams
     * @param SearchEngineResponse $searchEngineResponse
     * @param ProductsPerPage $productsPerPage
     * @param SortOrderConfig $selectedSortOrderConfig
     * @return HttpResponse
     */
    public function buildPageContent(
        PageMetaInfoSnippetContent $metaInfo,
        Context $context,
        array $keyGeneratorParams,
        SearchEngineResponse $searchEngineResponse,
        ProductsPerPage $productsPerPage,
        SortOrderConfig $selectedSortOrderConfig
    ) : HttpResponse {
        $productIds = $searchEngineResponse->getProductIds();
        $facetFieldCollection = $searchEngineResponse->getFacetFieldCollection();

        $this->addFilterNavigationSnippetToPageBuilder($facetFieldCollection);
        $this->addProductsInListingToPageBuilder($context, ...$productIds);
        $this->addPaginationSnippetsToPageBuilder($searchEngineResponse, $productsPerPage);
        $this->addSortOrderSnippetToPageBuilder($selectedSortOrderConfig);
        $this->addTranslationsToPageBuilder($context);
        $this->addRobotsMetaTagSnippetToHeadContainer();

        return $this->pageBuilder->buildPage($metaInfo, $context, $keyGeneratorParams);
    }

    private function addFilterNavigationSnippetToPageBuilder(FacetFieldCollection $facetFieldCollection)
    {
        $facetFields = $facetFieldCollection->jsonSerialize();
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

    private function addProductsInListingToPageBuilder(Context $context, ProductId ...$productIds)
    {
        $productData = $this->productJsonService->get($context, ...$productIds);
        $this->addDynamicSnippetToPageBuilder('product_grid', json_encode($productData));
    }

    private function addPaginationSnippetsToPageBuilder(
        SearchEngineResponse $searchEngineResponse,
        ProductsPerPage $productsPerPage
    ) {
        $this->addDynamicSnippetToPageBuilder(
            'total_number_of_results',
            $searchEngineResponse->getTotalNumberOfResults()
        );
        $this->addDynamicSnippetToPageBuilder('products_per_page', json_encode($productsPerPage));
    }

    private function addSortOrderSnippetToPageBuilder(SortOrderConfig $selectedSortOrderConfig)
    {
        $sortOrderConfig = $this->getSortOrderConfigsWithGivenConfigSelected($selectedSortOrderConfig);
        $this->addDynamicSnippetToPageBuilder('sort_order_config', json_encode($sortOrderConfig));
    }

    /**
     * @param SortOrderConfig $selectedSortOrderConfig
     * @return SortOrderConfig[]
     */
    private function getSortOrderConfigsWithGivenConfigSelected(SortOrderConfig $selectedSortOrderConfig) : array
    {
        return array_map(function (SortOrderConfig $sortOrderConfig) use ($selectedSortOrderConfig) {
            if ($sortOrderConfig->getAttributeCode() == $selectedSortOrderConfig->getAttributeCode()) {
                return $selectedSortOrderConfig;
            }

            if ($sortOrderConfig->isSelected() === true) {
                return SortOrderConfig::create(
                    $sortOrderConfig->getAttributeCode(),
                    $sortOrderConfig->getSelectedDirection()
                );
            }

            return $sortOrderConfig;
        }, $this->sortOrderConfigs);
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
