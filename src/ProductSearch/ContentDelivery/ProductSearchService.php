<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\Http\ContentDelivery\ProductJsonService\ProductJsonService;
use LizardsAndPumpkins\ProductSearch\QueryOptions;

class ProductSearchService
{
    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    /**
     * @var ProductJsonService
     */
    private $productJsonService;

    public function __construct(DataPoolReader $dataPoolReader, ProductJsonService $productJsonService)
    {
        $this->dataPoolReader = $dataPoolReader;
        $this->productJsonService = $productJsonService;
    }

    /**
     * @param string $queryString
     * @param QueryOptions $queryOptions
     * @return array[]
     */
    public function query(string $queryString, QueryOptions $queryOptions) : array
    {
        $searchEngineResponse = $this->dataPoolReader->getSearchResultsMatchingString($queryString, $queryOptions);
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
