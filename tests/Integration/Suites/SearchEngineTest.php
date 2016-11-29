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
        $sortBy = SortBy::createUnselected(AttributeCode::fromString('sku'), SortDirection::create(SortDirection::ASC));

        /** @var SearchEngine $searchEngine */
        $searchEngine = $this->factory->getSearchEngine();

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

        $this->assertContains('M29540', $searchEngineResponse->getProductIds());
    }
}
