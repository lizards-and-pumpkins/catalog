<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngineResponse;
use LizardsAndPumpkins\ContentDelivery\PageBuilder;
use LizardsAndPumpkins\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Product\PriceSnippetRenderer;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductId;
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
        $productIds = $searchEngineResponse->getProductIds();

        if (count($productIds) > 0) {
            $facetFieldCollection = $searchEngineResponse->getFacetFieldCollection();

            $this->addFilterNavigationSnippetToPageBuilder($facetFieldCollection);
            $this->addProductsInListingToPageBuilder($context, ...$productIds);
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

    private function addProductsInListingToPageBuilder(Context $context, ProductId ...$productIds)
    {
        $productInListingSnippetKeys = $this->getProductInListingSnippetKeys($context, ...$productIds);
        $priceSnippetKeys = $this->getPriceSnippetKeys($context, ...$productIds);
        $specialPriceSnippetKeys = $this->getSpecialPriceSnippetKeys($context, ...$productIds);

        $snippetKeysToFetch = array_merge($productInListingSnippetKeys, $priceSnippetKeys, $specialPriceSnippetKeys);
        $snippets = $this->dataPoolReader->getSnippets($snippetKeysToFetch);

        $this->addProductGridSnippetToPageBuilder($snippets, $productInListingSnippetKeys);
        $this->addProductPricesSnippetToPageBuilder($snippets, $priceSnippetKeys, $specialPriceSnippetKeys);
    }

    /**
     * @param Context $context
     * @param ProductId[] $productIds
     * @return string[]
     */
    private function getProductInListingSnippetKeys(Context $context, ProductId ...$productIds)
    {
        return $this->getSnippetKeysForGivenSnippetCode(
            ProductInListingSnippetRenderer::CODE,
            $context,
            ...$productIds
        );
    }

    /**
     * @param Context $context
     * @param ProductId[] $productIds
     * @return string[]
     */
    private function getPriceSnippetKeys(Context $context, ProductId ...$productIds)
    {
        return $this->getSnippetKeysForGivenSnippetCode(PriceSnippetRenderer::PRICE, $context, ...$productIds);
    }

    /**
     * @param Context $context
     * @param ProductId[] $productIds
     * @return string[]
     */
    private function getSpecialPriceSnippetKeys(Context $context, ProductId ...$productIds)
    {
        return $this->getSnippetKeysForGivenSnippetCode(PriceSnippetRenderer::SPECIAL_PRICE, $context, ...$productIds);
    }

    /**
     * @param string $snippetCode
     * @param Context $context
     * @param ProductId[] $productIds
     * @return string[]
     */
    private function getSnippetKeysForGivenSnippetCode($snippetCode, Context $context, ProductId ...$productIds)
    {
        $keyGenerator = $this->keyGeneratorLocator->getKeyGeneratorForSnippetCode($snippetCode);

        return array_map(function (ProductId $productId) use ($keyGenerator, $context) {
            return $keyGenerator->getKeyForContext($context, [Product::ID => $productId]);
        }, $productIds);
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
