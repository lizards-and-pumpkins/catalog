<?php

namespace Brera\Product;

use Brera\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use Brera\UrlKey;

class ProductListingMetaInfoSource
{
    /**
     * @var UrlKey
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
     * @param UrlKey $urlKey
     * @param string[] $contextData
     * @param SearchCriteria $criteria
     */
    public function __construct(UrlKey $urlKey, array $contextData, SearchCriteria $criteria)
    {
        $this->urlKey = $urlKey;
        $this->contextData = $contextData;
        $this->criteria = $criteria;
    }

    /**
     * @return UrlKey
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
