<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderConfig;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderDirection;
use LizardsAndPumpkins\Context\Locale\ContextLocale;
use LizardsAndPumpkins\Context\Website\ContextWebsite;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestSimpleField;
use LizardsAndPumpkins\ProductSearch\QueryOptions;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionAnything;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\Util\Factory\SampleMasterFactory;

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

        $queryOptions = QueryOptions::create(
            $selectedFilters,
            $context,
            $facetFieldRequest,
            $rowsPerPage,
            $pageNumber,
            $sortOrderConfig
        );

        $searchEngineResponse = $searchEngine->query(SearchCriterionAnything::create(), $queryOptions);

        $this->assertContains('M29540', $searchEngineResponse->getProductIds());
    }
}
