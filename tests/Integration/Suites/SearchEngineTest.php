<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig;
use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderDirection;
use LizardsAndPumpkins\Context\ContextBuilder\ContextLocale;
use LizardsAndPumpkins\Context\ContextBuilder\ContextWebsite;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestSimpleField;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionAnything;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\Product\AttributeCode;

class SearchEngineTest extends AbstractIntegrationTest
{
    /**
     * @var SampleMasterFactory
     */
    private $factory;

    public function testItMatchesVariationAttributes()
    {
        $this->factory = $this->prepareIntegrationTestMasterFactory();
        $this->importCatalogFixture($this->factory);

        $context = $this->factory->createContextBuilder()->createContext([
            ContextLocale::CODE  => 'en_US',
            ContextWebsite::CODE => 'ru',
        ]);
        $facetFieldRequest = new FacetFiltersToIncludeInResult(
            new FacetFilterRequestSimpleField(AttributeCode::fromString('color'))
        );
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortOrderConfig = SortOrderConfig::create(
            AttributeCode::fromString('sku'),
            SortOrderDirection::create(SortOrderDirection::ASC)
        );

        /** @var SearchEngine $searchEngine */
        $searchEngine = $this->factory->getSearchEngine();

        $selectedFilters = ['color' => ['Red']];
        $searchEngineResponse = $searchEngine->getSearchDocumentsMatchingCriteria(
            SearchCriterionAnything::create(),
            $selectedFilters,
            $context,
            $facetFieldRequest,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );

        $this->assertContains('M29540', $searchEngineResponse->getProductIds());
    }
}
