<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\Locale\Locale;
use LizardsAndPumpkins\Context\Website\Website;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortBy;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortDirection;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestSimpleField;
use LizardsAndPumpkins\ProductSearch\QueryOptions;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionAnything;
use LizardsAndPumpkins\Import\Product\AttributeCode;

class SearchEngineTest extends AbstractIntegrationTest
{
    public function testItMatchesVariationAttributes(): void
    {
        $factory = $this->prepareIntegrationTestMasterFactory();
        $this->importCatalogFixture($factory, 'configurable_product_adipure.xml');

        $context = $factory->createContextBuilder()->createContext([
            Locale::CONTEXT_CODE => 'en_US',
            Website::CONTEXT_CODE => 'ru',
        ]);
        $facetFieldRequest = new FacetFiltersToIncludeInResult(
            new FacetFilterRequestSimpleField(AttributeCode::fromString('color'))
        );
        $rowsPerPage = 100;
        $pageNumber = 0;
        $sortBy = new SortBy(AttributeCode::fromString('sku'), SortDirection::create(SortDirection::ASC));

        $searchEngine = $factory->getSearchEngine();

        $selectedFilters = ['color' => ['Red']];

        $queryOptions = QueryOptions::create(
            $selectedFilters,
            $context,
            $facetFieldRequest,
            $rowsPerPage,
            $pageNumber,
            $sortBy
        );

        $searchEngineResponse = $searchEngine->query(new SearchCriterionAnything(), $queryOptions);

        $this->assertTrue(in_array('M29540', $searchEngineResponse->getProductIds()));
    }
}
