<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\Product\AttributeCode;

class FacetFilterConfig
{
    /**
     * @var AttributeCode
     */
    private $attributeCode;

    /**
     * @var FacetFieldRangeCollection
     */
    private $rangeCollection;

    public function __construct(AttributeCode $attributeCode)
    {
        $this->attributeCode = $attributeCode;
    }

    /**
     * @param AttributeCode $attributeCode
     * @param FacetFieldRangeCollection $facetFieldRangeCollection
     * @return FacetFilterConfig
     */
    public static function createRanged(
        AttributeCode $attributeCode,
        FacetFieldRangeCollection $facetFieldRangeCollection
    ) {
        $config = new self($attributeCode);
        $config->setRangeCollection($facetFieldRangeCollection);

        return $config;
    }

    /**
     * @return AttributeCode
     */
    public function getAttributeCode()
    {
        return $this->attributeCode;
    }

    /**
     * @return FacetFieldRangeCollection
     */
    public function getRangeCollection()
    {
        return $this->rangeCollection;
    }

    private function setRangeCollection(FacetFieldRangeCollection $facetFieldRangeCollection)
    {
        $this->rangeCollection = $facetFieldRangeCollection;
    }
}
