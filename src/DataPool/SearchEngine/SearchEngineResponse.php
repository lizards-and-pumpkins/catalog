<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\Product\ProductId;

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

    /**
     * @param FacetFieldCollection $facetFieldCollection
     * @param int $totalNumberOfResults
     * @param ProductId[] $productIds
     */
    public function __construct(
        FacetFieldCollection $facetFieldCollection,
        $totalNumberOfResults,
        ProductId ...$productIds
    ) {
        $this->facetFieldCollection = $facetFieldCollection;
        $this->totalNumberOfResults = $totalNumberOfResults;
        $this->productIds = $productIds;
    }

    /**
     * @return ProductId[]
     */
    public function getProductIds()
    {
        return $this->productIds;
    }

    /**
     * @return FacetFieldCollection
     */
    public function getFacetFieldCollection()
    {
        return $this->facetFieldCollection;
    }

    /**
     * @return int
     */
    public function getTotalNumberOfResults()
    {
        return $this->totalNumberOfResults;
    }
}
