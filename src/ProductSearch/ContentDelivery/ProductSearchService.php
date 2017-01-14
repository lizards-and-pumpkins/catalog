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

    public function query(SearchCriteria $searchCriteria, QueryOptions $queryOptions) : ProductSearchResult
    {
        $criteria = CompositeSearchCriterion::createAnd($searchCriteria, $this->globalProductListingCriteria);
        $searchEngineResponse = $this->dataPoolReader->getSearchResults($criteria, $queryOptions);

        $productIds = $searchEngineResponse->getProductIds();
        $productData = $this->productJsonService->get($queryOptions->getContext(), ...$productIds);

        return new ProductSearchResult(
            $searchEngineResponse->getTotalNumberOfResults(),
            $productData,
            $searchEngineResponse->getFacetFieldCollection()
        );
    }
}
