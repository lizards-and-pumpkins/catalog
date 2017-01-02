<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldCollection;

class ProductSearchResult implements \JsonSerializable
{
    /**
     * @var int
     */
    private $totalNumber;

    /**
     * @var array[]
     */
    private $productsData;

    /**
     * @var FacetFieldCollection
     */
    private $facetFieldCollection;

    /**
     * @param int $totalNumber
     * @param array[] $productsData
     * @param FacetFieldCollection $facetFieldCollection
     */
    public function __construct(int $totalNumber, array $productsData, FacetFieldCollection $facetFieldCollection)
    {
        $this->totalNumber = $totalNumber;
        $this->productsData = $productsData;
        $this->facetFieldCollection = $facetFieldCollection;
    }

    /**
     * @return mixed[]
     */
    function jsonSerialize() : array
    {
        return [
            'total' => $this->totalNumber,
            'data' => $this->productsData,
            'facets' => $this->facetFieldCollection->jsonSerialize(),
        ];
    }

    public function getTotalNumberOfResults() : int
    {
        return $this->totalNumber;
    }

    /**
     * @return array[]
     */
    public function getData() : array
    {
        return $this->productsData;
    }

    public function getFacetFieldCollection() : FacetFieldCollection
    {
        return $this->facetFieldCollection;
    }
}
