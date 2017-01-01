<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonService;
use LizardsAndPumpkins\ProductSearch\QueryOptions;

class ProductSearchService
{
    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    /**
     * @var SearchCriteria
     */
    private $globalProductListingCriteria;

    /**
     * @var ProductJsonService
     */
    private $productJsonService;

    public function __construct(
        DataPoolReader $dataPoolReader,
        SearchCriteria $globalProductListingCriteria,
        ProductJsonService $productJsonService
    ) {
        $this->dataPoolReader = $dataPoolReader;
        $this->globalProductListingCriteria = $globalProductListingCriteria;
        $this->productJsonService = $productJsonService;
    }

    /**
     * @param SearchCriteria $searchCriteria
     * @param QueryOptions $queryOptions
     * @return array[]
     */
    public function query(SearchCriteria $searchCriteria, QueryOptions $queryOptions) : array
    {
        $criteria = CompositeSearchCriterion::createAnd($searchCriteria, $this->globalProductListingCriteria);
        $searchEngineResponse = $this->dataPoolReader->getSearchResultsMatchingCriteria($criteria, $queryOptions);

        $productIds = $searchEngineResponse->getProductIds();

        if ([] === $productIds) {
            return ['total' => 0, 'data' => []];
        }

        return [
            'total' => $searchEngineResponse->getTotalNumberOfResults(),
            'data' => $this->productJsonService->get($queryOptions->getContext(), ...$productIds)
        ];
    }
}
