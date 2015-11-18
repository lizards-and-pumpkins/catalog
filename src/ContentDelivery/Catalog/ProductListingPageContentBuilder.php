<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineFacetFieldCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\PageBuilder;
use LizardsAndPumpkins\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Product\PriceSnippetRenderer;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductInListingSnippetRenderer;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator\SnippetKeyGeneratorLocator;

class ProductListingPageContentBuilder
{
    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    /**
     * @var SnippetKeyGeneratorLocator
     */
    private $keyGeneratorLocator;

    /**
     * @var PageBuilder
     */
    private $pageBuilder;

    /**
     * @var SortOrderConfig[]
     */
    private $sortOrderConfigs;

    public function __construct(
        DataPoolReader $dataPoolReader,
        SnippetKeyGeneratorLocator $keyGeneratorLocator,
        PageBuilder $pageBuilder,
        SortOrderConfig ...$sortOrderConfigs
    ) {
        $this->dataPoolReader = $dataPoolReader;
        $this->keyGeneratorLocator = $keyGeneratorLocator;
        $this->pageBuilder = $pageBuilder;
        $this->sortOrderConfigs = $sortOrderConfigs;
    }

    public function buildPageContent(
        PageMetaInfoSnippetContent $metaInfo,
        Context $context,
        array $keyGeneratorParams,
        SearchEngineResponse $searchEngineResponse,
        ProductsPerPage $productsPerPage,
        SortOrderConfig $selectedSortOrderConfig
    ) {
        $searchDocumentCollection = $searchEngineResponse->getSearchDocuments();

        if (count($searchDocumentCollection) > 0) {
            $facetFieldCollection = $searchEngineResponse->getFacetFieldCollection();

            $this->addFilterNavigationSnippetToPageBuilder($facetFieldCollection);
            $this->addProductsInListingToPageBuilder($context, $searchDocumentCollection);
            $this->addPaginationSnippetsToPageBuilder($searchEngineResponse, $productsPerPage);
            $this->addSortOrderSnippetToPageBuilder($selectedSortOrderConfig);
        }

        return $this->pageBuilder->buildPage($metaInfo, $context, $keyGeneratorParams);
    }

    private function addFilterNavigationSnippetToPageBuilder(SearchEngineFacetFieldCollection $facetFieldCollection)
    {
        $snippetCode = 'filter_navigation';
        $snippetContents = json_encode($facetFieldCollection);

        $this->addDynamicSnippetToPageBuilder($snippetCode, $snippetContents);
    }

    private function addProductsInListingToPageBuilder(
        Context $context,
        SearchDocumentCollection $searchDocumentCollection
    ) {
        $documents = $searchDocumentCollection->getDocuments();
        $productInListingSnippetKeys = $this->getProductInListingSnippetKeysForSearchDocuments($context, ...$documents);
        $productPriceSnippetKeys = $this->getProductPriceSnippetKeysForSearchDocuments($context, ...$documents);

        $snippetKeysToFetch = array_merge($productInListingSnippetKeys, $productPriceSnippetKeys);
        $snippets = $this->dataPoolReader->getSnippets($snippetKeysToFetch);

        $this->addProductGridSnippetToPageBuilder($snippets, $productInListingSnippetKeys);
        $this->addProductPricesSnippetToPageBuilder($snippets, $productPriceSnippetKeys);
    }

    /**
     * @param Context $context
     * @param SearchDocument[] $searchDocuments
     * @return string[]
     */
    private function getProductInListingSnippetKeysForSearchDocuments(
        Context $context,
        SearchDocument ...$searchDocuments
    ) {
        return $this->getSnippetKeysForGivenSnippetCode(
            ProductInListingSnippetRenderer::CODE,
            $context,
            ...$searchDocuments
        );
    }

    /**
     * @param Context $context
     * @param SearchDocument[] $searchDocuments
     * @return string[]
     */
    private function getProductPriceSnippetKeysForSearchDocuments(Context $context, SearchDocument ...$searchDocuments)
    {
        return $this->getSnippetKeysForGivenSnippetCode(PriceSnippetRenderer::CODE, $context, ...$searchDocuments);
    }

    /**
     * @param string $snippetCode
     * @param Context $context
     * @param SearchDocument[] $searchDocuments
     * @return string[]
     */
    private function getSnippetKeysForGivenSnippetCode(
        $snippetCode,
        Context $context,
        SearchDocument ...$searchDocuments
    ) {
        $keyGenerator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode($snippetCode);
        return array_map(function (SearchDocument $searchDocument) use ($keyGenerator, $context) {
            return $keyGenerator->getKeyForContext($context, [Product::ID => $searchDocument->getProductId()]);
        }, $searchDocuments);
    }

    /**
     * @param string[] $snippets
     * @param string[] $productInListingSnippetKeys
     */
    private function addProductGridSnippetToPageBuilder($snippets, $productInListingSnippetKeys)
    {
        $this->addCombinedSnippetsWithGivenKeysToPageBuilder($snippets, $productInListingSnippetKeys, 'product_grid');
    }

    /**
     * @param string[] $snippets
     * @param string[] $productPriceSnippetKeys
     */
    private function addProductPricesSnippetToPageBuilder($snippets, $productPriceSnippetKeys)
    {
        $this->addCombinedSnippetsWithGivenKeysToPageBuilder($snippets, $productPriceSnippetKeys, 'product_prices');
    }

    /**
     * @param string[] $allSnippets
     * @param string[] $snippetKeysToUse
     * @param string $combinedSnippetKey
     */
    private function addCombinedSnippetsWithGivenKeysToPageBuilder($allSnippets, $snippetKeysToUse, $combinedSnippetKey)
    {
        $matchingSnippets = array_intersect_key($allSnippets, array_flip($snippetKeysToUse));
        $combinedSnippetContent = '[' . implode(',', $matchingSnippets) . ']';
        $this->addDynamicSnippetToPageBuilder($combinedSnippetKey, $combinedSnippetContent);
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
}
