<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\Product\Exception\ProductListingAttributeNotFoundException;
use LizardsAndPumpkins\UrlKey;

class ProductListing
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
     * @var ProductListingAttributeList
     */
    private $attributeList;

    /**
     * @var SearchCriteria
     */
    private $criteria;

    /**
     * @param UrlKey $urlKey
     * @param string[] $contextData
     * @param ProductListingAttributeList $attributeList
     * @param SearchCriteria $criteria
     */
    public function __construct(
        UrlKey $urlKey,
        array $contextData,
        ProductListingAttributeList $attributeList,
        SearchCriteria $criteria
    ) {
        $this->urlKey = $urlKey;
        $this->contextData = $contextData;
        $this->criteria = $criteria;
        $this->attributeList = $attributeList;
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

    /**
     * @param string $code
     * @return bool
     */
    public function hasAttribute($code)
    {
        return $this->attributeList->hasAttribute($code);
    }

    /**
     * @param string $code
     * @return bool|float|int|string
     */
    public function getAttributeValueByCode($code)
    {
        if (!$this->hasAttribute($code)) {
            throw new ProductListingAttributeNotFoundException(
                sprintf('Product list attribute with code "%s" is not found.', $code)
            );
        }

        return $this->attributeList->getAttributeValueByCode($code);
    }
}
