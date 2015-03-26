<?php

namespace Brera\Product;

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
     * @var string[]
     */
    private $criteria;

    /**
     * @param string $urlKey
     * @param string[] $contextData
     * @param string[] $criteria
     */
    public function __construct($urlKey, array $contextData, array $criteria)
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
}
