<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Import\Product\ProductId;

class SearchEngineResponse
{
    /**
     * @var FacetFieldCollection
     */
    private $facetFieldCollection;

    /**
     * @var int
     */
    private $totalNumberOfResults;

    /**
     * @var ProductId[]
     */
    private $productIds;

    public function __construct(
        FacetFieldCollection $facetFieldCollection,
        int $totalNumberOfResults,
        ProductId ...$productIds
    ) {
        $this->facetFieldCollection = $facetFieldCollection;
        $this->totalNumberOfResults = $totalNumberOfResults;
        $this->productIds = $productIds;
    }

    /**
     * @return ProductId[]
     */
    public function getProductIds() : array
    {
        return $this->productIds;
    }

    public function getFacetFieldCollection() : FacetFieldCollection
    {
        return $this->facetFieldCollection;
    }

    public function getTotalNumberOfResults() : int
    {
        return $this->totalNumberOfResults;
    }
}
