<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\Locale\Locale;
use LizardsAndPumpkins\Context\Website\Website;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderConfig;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderDirection;
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
            Locale::CONTEXT_CODE => 'en_US',
            Website::CONTEXT_CODE => 'ru',
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
