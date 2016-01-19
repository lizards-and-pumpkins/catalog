<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\ContentDelivery\Catalog\Search\SearchFieldToRequestParamMap;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder\ContextLocale;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetField;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\ContentDelivery\PageBuilder;
use LizardsAndPumpkins\DefaultHttpResponse;
use LizardsAndPumpkins\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Renderer\Translation\Translator;
use LizardsAndPumpkins\Renderer\Translation\TranslatorRegistry;

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
     * @return DefaultHttpResponse
     */
    public function buildPageContent(
        PageMetaInfoSnippetContent $metaInfo,
        Context $context,
        array $keyGeneratorParams,
        SearchEngineResponse $searchEngineResponse,
        ProductsPerPage $productsPerPage,
        SortOrderConfig $selectedSortOrderConfig
    ) {
        $productIds = $searchEngineResponse->getProductIds();

        if (count($productIds) > 0) {
            $facetFieldCollection = $searchEngineResponse->getFacetFieldCollection();

            $this->addFilterNavigationSnippetToPageBuilder($facetFieldCollection);
            $this->addProductsInListingToPageBuilder($context, ...$productIds);
            $this->addPaginationSnippetsToPageBuilder($searchEngineResponse, $productsPerPage);
            $this->addSortOrderSnippetToPageBuilder($selectedSortOrderConfig);
            $this->addFilterAttributeTranslationsToPageBuilder($facetFieldCollection, $context);
        }

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
    private function replaceInternalWithExternalFieldNames(array $facetFields)
    {
        return array_reduce(array_keys($facetFields), function ($carry, $fieldName) use ($facetFields) {
            $parameterName = $this->searchFieldToRequestParamMap->getQueryParameterName($fieldName);
            return array_merge($carry, [$parameterName => $facetFields[$fieldName]]);
        }, []);
    }

    private function addProductsInListingToPageBuilder(Context $context, ProductId ...$productIds)
    {
        $productData = $this->productJsonService->get(...$productIds);
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
    private function getSortOrderConfigsWithGivenConfigSelected(SortOrderConfig $selectedSortOrderConfig)
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
     * @param string $snippetContents
     */
    private function addDynamicSnippetToPageBuilder($snippetCode, $snippetContents)
    {
        $snippetCodeToKeyMap = [$snippetCode => $snippetCode];
        $snippetKeyToContentMap = [$snippetCode => $snippetContents];

        $this->pageBuilder->addSnippetsToPage($snippetCodeToKeyMap, $snippetKeyToContentMap);
    }

    private function addFilterAttributeTranslationsToPageBuilder(
        FacetFieldCollection $facetFieldCollection,
        Context $context
    ) {
        $translator = $this->translatorRegistry->getTranslatorForLocale($context->getValue(ContextLocale::CODE));

        $facetFieldAttributesTranslations = $this->getFilterAttributesTranslations($facetFieldCollection, $translator);
        $sortingAttributeTranslations = $this->getSortingAttributesTranslations($translator);

        $translationsJson = json_encode(array_merge($facetFieldAttributesTranslations, $sortingAttributeTranslations));

        $this->addDynamicSnippetToPageBuilder('attribute_translation', $translationsJson);
    }

    /**
     * @param FacetFieldCollection $facetFields
     * @param Translator $translator
     * @return string[]
     */
    private function getFilterAttributesTranslations(FacetFieldCollection $facetFields, Translator $translator)
    {
        $facetFields = $facetFields->getFacetFields();

        return array_reduce($facetFields, function (array $carry, FacetField $facetField) use ($translator) {
            $attributeCodeString = (string) $facetField->getAttributeCode();
            return array_merge($carry, [$attributeCodeString => $translator->translate($attributeCodeString)]);
        }, []);
    }

    /**
     * @param Translator $translator
     * @return string[]
     */
    private function getSortingAttributesTranslations(Translator $translator)
    {
        return array_reduce(
            $this->sortOrderConfigs,
            function (array $carry, SortOrderConfig $sortOrderConfig) use ($translator) {
                $attributeCodeString = (string) $sortOrderConfig->getAttributeCode();

                return array_merge($carry, [$attributeCodeString => $translator->translate($attributeCodeString)]);
            },
            []
        );
    }
}
