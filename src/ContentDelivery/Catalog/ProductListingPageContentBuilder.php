<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\ContentDelivery\PageBuilder;
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

    private function addFilterNavigationSnippetToPageBuilder(FacetFieldCollection $facetFieldCollection)
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
        $priceSnippetKeys = $this->getPriceSnippetKeysForSearchDocuments($context, ...$documents);
        $specialPriceSnippetKeys = $this->getSpecialPriceSnippetKeysForSearchDocuments($context, ...$documents);

        $snippetKeysToFetch = array_merge($productInListingSnippetKeys, $priceSnippetKeys, $specialPriceSnippetKeys);
        $snippets = $this->dataPoolReader->getSnippets($snippetKeysToFetch);

        $this->addProductGridSnippetToPageBuilder($snippets, $productInListingSnippetKeys);
        $this->addProductPricesSnippetToPageBuilder($snippets, $priceSnippetKeys, $specialPriceSnippetKeys);
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
     * @param SearchDocument[] $documents
     * @return string[]
     */
    private function getPriceSnippetKeysForSearchDocuments(Context $context, SearchDocument ...$documents)
    {
        return $this->getSnippetKeysForGivenSnippetCode(PriceSnippetRenderer::PRICE, $context, ...$documents);
    }

    /**
     * @param Context $context
     * @param SearchDocument[] $documents
     * @return string[]
     */
    private function getSpecialPriceSnippetKeysForSearchDocuments(Context $context, SearchDocument ...$documents)
    {
        return $this->getSnippetKeysForGivenSnippetCode(PriceSnippetRenderer::SPECIAL_PRICE, $context, ...$documents);
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
        $matchingSnippets = array_intersect_key($snippets, array_flip($productInListingSnippetKeys));
        $combinedSnippetContent = '[' . implode(',', $matchingSnippets) . ']';
        $this->addDynamicSnippetToPageBuilder('product_grid', $combinedSnippetContent);
    }

    /**
     * @param string[] $snippets
     * @param string[] $priceSnippetKeys
     * @param string[] $specialPriceSnippetKeys
     */
    private function addProductPricesSnippetToPageBuilder($snippets, $priceSnippetKeys, $specialPriceSnippetKeys)
    {
        $prices = array_map(function ($index) use ($snippets, $priceSnippetKeys, $specialPriceSnippetKeys) {
            return $this->getPriceSnippetsArray($snippets, $priceSnippetKeys[$index], $specialPriceSnippetKeys[$index]);
        }, array_keys($priceSnippetKeys));

        $this->addDynamicSnippetToPageBuilder('product_prices', json_encode($prices));
    }

    /**
     * @param string[] $snippets
     * @param string $priceSnippetKey
     * @param string $specialPriceSnippetKey
     * @return string[]
     */
    private function getPriceSnippetsArray(array $snippets, $priceSnippetKey, $specialPriceSnippetKey)
    {
        $price = [];

        if (isset($snippets[$priceSnippetKey])) {
            $price[] = $snippets[$priceSnippetKey];
        }

        if (isset($snippets[$specialPriceSnippetKey])) {
            $price[] = $snippets[$specialPriceSnippetKey];
        }

        return $price;
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
