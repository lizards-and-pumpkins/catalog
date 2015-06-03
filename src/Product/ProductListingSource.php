<?php

namespace Brera\Product;

use Brera\DataPool\SearchEngine\SearchCriteria;
use Brera\ProjectionSourceData;

class ProductListingSource implements ProjectionSourceData
{
    /**
     * @var string
     */
    private $urlKey;

    /**
     * @var string[]
     */
    private $contextData;

    /**
     * @var SearchCriteria
     */
    private $criteria;

    /**
     * @param string $urlKey
     * @param string[] $contextData
     * @param SearchCriteria $criteria
     */
    public function __construct($urlKey, array $contextData, SearchCriteria $criteria)
    {
        $this->urlKey = $urlKey;
        $this->contextData = $contextData;
        $this->criteria = $criteria;
    }

    /**
     * @return string
     */
    public function getUrlKey()
    {
        return $this->urlKey;
    }

    /**
     * @return string[]
     */
    public function getContextData()
    {
        return $this->contextData;
    }

    /**
     * @return SearchCriteria
     */
    public function getCriteria()
    {
        return $this->criteria;
    }
}
